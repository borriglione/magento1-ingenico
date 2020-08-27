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

class Netresearch_OPS_Block_Info_BankTransfer extends Netresearch_OPS_Block_Info_Redirect
{
    /**
     * init ops payment banktransfer information block
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('ops/info/banktransfer.phtml');
    }

    /**
     * @return array|mixed|null
     */
    public function getCollectorIbanParameter()
    {
        return $this->getInfo()
                    ->getAdditionalInformation(Netresearch_OPS_Model_Payment_BankTransfer::OPS_COLLECTOR_IBAN);
    }

    /**
     * @return array|mixed|null
     */
    public function getCollectorBicParameter()
    {
        return $this->getInfo()
                    ->getAdditionalInformation(Netresearch_OPS_Model_Payment_BankTransfer::OPS_COLLECTOR_BIC);
    }

    /**
     * @return array|mixed|null
     */
    public function getPaymentReferenceParameter()
    {
        return $this->getInfo()
                    ->getAdditionalInformation(Netresearch_OPS_Model_Payment_BankTransfer::OPS_PAYMENT_REFERENCE);
    }

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('ops/info/pdf/banktransfer.phtml');
        return $this->toHtml();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isEmailAction()
    {
        return $this->getRequest()->getActionName() === 'email';
    }

    /**
     * @return bool
     */
    public function isPendingPayment()
    {
        return $this->getInfo()->getOrder()->getStatus() === 'pending_payment';
    }
}
