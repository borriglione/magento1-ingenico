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
 * Netresearch_OPS_Model_Cron_PaymentCancellation
 *
 * @package
 * @copyright 2018 Netresearch
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @author    Andreas Müller <andreas.mueller@netresearch.de>
 * @license   OSL 3.0
 */

class Netresearch_OPS_Model_Cron_PaymentCancellation
{
    const MESSAGE_TEMPLATE =
        '%d orders were cancelled. %d orders could not be cancelled.';

    /** @var   Netresearch_OPS_Model_Config */
    protected $config;

    /** @var Netresearch_OPS_Model_Status_Update */
    protected $statusUpdate;

    /** @var Netresearch_OPS_Helper_Payment */
    protected $paymentHelper;

    /**
     * Netresearch_OPS_Model_Cron_PaymentCancellation constructor.
     */
    public function __construct()
    {
        $this->config = Mage::getModel('ops/config');
        $this->statusUpdate = Mage::getModel('ops/status_update');
        $this->paymentHelper = Mage::helper('ops/payment');
    }

    /**
     * Cancel old orders.
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @throws \Exception
     */
    public function execute(Mage_Cron_Model_Schedule $schedule)
    {
        $failedCancellations = array();
        $sentCancellations   = array();

        /** @var Mage_Sales_Model_Resource_Order_Collection $filteredOrderCollection */
        $filteredOrderCollection = $this->getFilteredOrderCollection();
        foreach ($filteredOrderCollection as $order) {
            if (!$this->appliesForCancellation($order)) {
                continue;
            }

            /** @var Netresearch_OPS_Model_Status_Update $statusUpdate */
            $opsResponse = $this->statusUpdate->updateStatusFor($order)->getOpsResponse();
            $opsStatus = isset($opsResponse['STATUS']) ? $opsResponse['STATUS'] : null;

            if ($this->paymentHelper->isPaymentInvalid($opsStatus)) {
                try {
                    $order->cancel();
                    $sentCancellations[$order->getIncrementId()] = $order;
                } catch (\Exception $exception) {
                    $failedCancellations[$order->getIncrementId()] = $exception->getMessage();
                }
            }
        }

        $filteredOrderCollection->save();

        $scheduleMessage = sprintf(
            self::MESSAGE_TEMPLATE,
            count($sentCancellations),
            count($failedCancellations)
        );
        $schedule->setMessages($scheduleMessage);
    }

    /**
     * Get order collection filtered by enabled stores and payment method
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
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
        $orderCollection
            ->addFieldToFilter('payment.method', array("like" => '%ops_%'));

        return $orderCollection;
    }


    /**
     * @param Mage_Sales_Model_Order $order
     * @return bool
     * @throws \Exception
     */
    public function appliesForCancellation(Mage_Sales_Model_Order $order)
    {
        $daysTillCancellation = $this->config->getPaymentCancellationDays();
        $orderReminderDate = $this->config->isPaymentReminderEnabled() ? $this->config->getPaymentReminderDays(): 0;

        $dateNow = new \DateTime();
        try {
            $orderDate = new \DateTime($order->getCreatedAt());
            $orderDate
                ->add(new \DateInterval("P{$orderReminderDate}D"))
                ->add(new \DateInterval("P{$daysTillCancellation}D"));
            $isApplicable = $dateNow->getTimestamp() >= $orderDate->getTimestamp();
        } catch  (\Exception $exception){
            $isApplicable = false;
            Mage::logException($exception);
        }

        return $isApplicable;
    }
}
