<?php
/**
 * Netresearch_OPS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to
 * newer versions in the future.
 *
 * @copyright Copyright (c) 2018 Netresearch GmbH & Co. KG (http://www.netresearch.de/)
 * @license   Open Software License (OSL 3.0)
 * @link      http://opensource.org/licenses/osl-3.0.php
 */

/**
 * Netresearch_OPS_Model_Cron_PaymentReminder
 *
 * @package
 * @copyright 2018 Netresearch
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @license   OSL 3.0
 */

class Netresearch_OPS_Model_Cron_PaymentReminder
{
    const MESSAGE_TEMPLATE = '%d order payment reminders were send. %d reminders could not be send.';

    /** @var Netresearch_OPS_Model_Config */
    protected $config;

    /** @var Netresearch_OPS_Model_Status_Update */
    protected $statusUpdate;

    /** @var Netresearch_OPS_Helper_Payment */
    protected $paymentHelper;

    /** @var Netresearch_OPS_Model_Payment_Features_PaymentEmail */
    protected $paymentEmail;

    /**
     * Netresearch_OPS_Model_Cron_PaymentReminder constructor.
     */
    public function __construct()
    {
        $this->config = Mage::getModel('ops/config');
        $this->statusUpdate = Mage::getModel('ops/status_update');
        $this->paymentHelper = Mage::helper('ops/payment');
        $this->paymentEmail =Mage::getModel('ops/payment_features_paymentEmail');
    }

    /**
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function execute(Mage_Cron_Model_Schedule $schedule)
    {
        $failedOrderReminders = array();
        $sentOrderReminders   = array();
        /** @var Mage_Sales_Model_Resource_Order_Collection $filteredOrderCollection */
        $filteredCollection = $this->getFilteredOrderCollection();

        foreach ($filteredCollection as $order) {
            if (!$this->appliesForReminder($order)) {
                continue;
            }

            $opsResponse = $this->statusUpdate->updateStatusFor($order)->getOpsResponse();
            $opsStatus = isset($opsResponse['STATUS']) ? $opsResponse['STATUS'] : null;
            if ($this->paymentHelper->isPaymentInvalid($opsStatus) &&
                $this->paymentEmail->isAvailableForOrder($order)
            ) {
                try {
                    /** @var Mage_Sales_Model_Order $order */
                    $this->paymentEmail->resendPaymentInfo($order);
                    $order->getPayment()->setAdditionalInformation('reminder_sent', 1);
                    $sentOrderReminders[$order->getIncrementId()] = $order;
                } catch (\Exception $exception) {
                    $failedOrderReminders[$order->getIncrementId()] = $exception->getMessage();
                }
            }
        }

        $filteredCollection->save();

        $scheduleMessage = sprintf(
            self::MESSAGE_TEMPLATE,
            count($sentOrderReminders),
            count($failedOrderReminders)
        );
        $schedule->setMessages($scheduleMessage);
    }


    /**
     * Get order collection filtered by enabled stores and payment method
     *
     * @return Mage_Sales_Model_Resource_Order_Collection|object
     */
    public function getFilteredOrderCollection()
    {
        $enabledStoreIds = array();
        foreach (Mage::app()->getStores(true, true) as $store) {
            if (!$this->config->isPaymentCancellationEnabled($store->getId())) {
                continue;
            }

            $enabledStoreIds[] = $store->getId();
        }

        /** @var Mage_Sales_Model_Resource_Order_Collection $orderCollection */
        $orderCollection = Mage::getModel('sales/order')->getCollection();
        $orderCollection
            ->addAttributeToSelect('*')
            ->addFieldToFilter('store_id', array('in' => $enabledStoreIds))
            ->addFieldToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_PENDING_PAYMENT));
        $orderCollection
            ->getSelect()
            ->join(
                array("payment" => "sales_flat_order_payment"),
                'main_table.entity_id = payment.parent_id',
                array('payment_method' => 'payment.method')
            );
        $orderCollection->addFieldToFilter('payment.method', array("like" => '%ops_%'));

        return $orderCollection;
    }

    /**
     * Check wheter order is old enough to qualify for a reminder email.
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     * @throws \Exception
     */
    public function appliesForReminder(Mage_Sales_Model_Order $order)
    {
        $reminderDays = $this->config->getPaymentReminderDays();
        $dateNow = new \DateTime();

        try {
            $orderReminderDate = new \DateTime($order->getCreatedAt());
            $orderReminderDate->add(
                new \DateInterval("P{$reminderDays}D")
            );
            $isOldEnough = $orderReminderDate->getTimestamp() < $dateNow->getTimestamp();
        } catch(\Exception $exception) {
            $isOldEnough = false;
            Mage::logException($exception);
        }

        $reminderSend = $order->getPayment()->getAdditionalInformation('reminder_sent') === 1;

        return $isOldEnough && !$reminderSend;
    }
}
