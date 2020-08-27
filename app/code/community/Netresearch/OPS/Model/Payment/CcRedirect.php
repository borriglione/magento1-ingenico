<?php

/**
 * Class Netresearch_OPS_Model_Payment_CcRedirect
 */
class Netresearch_OPS_Model_Payment_CcRedirect extends Netresearch_OPS_Model_Payment_Abstract
{
    const CODE = 'ops_cc_redirect';

    /** info source path */
    protected $_infoBlockType = 'ops/info_cc';

    /** @var string $_formBlockType define a specific form block */
    protected $_formBlockType = 'ops/form_cc';

    /** payment code */
    protected $_code = self::CODE;



    /**
     * @param null $payment
     * @return string
     */
    public function getOpsCode($payment = null)
    {
        $opsBrand = $this->getOpsBrand($payment);
        if ('PostFinance card' == $opsBrand) {
            return 'PostFinance Card';
        }

        if ('UNEUROCOM' == $this->getOpsBrand($payment)) {
            return 'UNEUROCOM';
        }

        return 'CreditCard';
    }

    /**
     * @inheritdoc
     */
    public function getMethodDependendFormFields($order, $requestParams = null)
    {
        $formFields = parent::getMethodDependendFormFields($order, $requestParams);
        if ($this->getConfig()->getCreditDebitSplit($order->getStoreId())) {
            $formFields['CREDITDEBIT'] = "C";
        }

        $alias = $order->getPayment()->getAdditionalInformation('alias') ?: '';
        $formFields['ALIAS'] = $alias;

        if ($this->getConfigData('active_alias')) {
            if ($alias) {
                $formFields['ALIASOPERATION'] = "BYPSP";
                $formFields['ECI'] = 9;
                $formFields['ALIASUSAGE'] = $this->getConfig()->getAliasUsageForExistingAlias(
                    $order->getPayment()->getMethodInstance()->getCode(),
                    $order->getStoreId()
                );
            } else {
                $formFields['ALIASOPERATION'] = "BYPSP";
                $formFields['ALIASUSAGE'] = $this->getConfig()->getAliasUsageForNewAlias(
                    $order->getPayment()->getMethodInstance()->getCode(),
                    $order->getStoreId()
                );
            }
        }

        return $formFields;
    }

}
