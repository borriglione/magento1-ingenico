<?php

/**
 * Netresearch_OPS_Model_Payment_Debitcard
 *
 * @package
 * @copyright 2018 Netresearch
 * @author    Sebastian Ertner <sebastian.ertner@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_DebitcardRedirect extends Netresearch_OPS_Model_Payment_Abstract
{

    const CODE = 'ops_dc_redirect';

    /** payment code */
    protected $_code = self::CODE;

    /** info source path */
    protected $_infoBlockType = 'ops/info_cc';

    /** @var string $_formBlockType define a specific form block */
    protected $_formBlockType = 'ops/form_cc';


    /**
     * @param null $payment
     * @return string
     */
    public function getOpsCode($payment = null)
    {
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

