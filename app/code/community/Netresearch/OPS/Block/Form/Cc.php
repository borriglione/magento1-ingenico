<?php

/**
 * Netresearch_OPS_Block_Form_OpsId
 *
 * @package   OPS
 * @copyright 2012 Netresearch App Factory AG <http://www.netresearch.de>
 * @author    Thomas Birke <thomas.birke@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Block_Form_Cc extends Netresearch_OPS_Block_Form
{

    protected $aliasDataForCustomer = array();

    /**
     * CC Payment Template
     */
    const FRONTEND_TEMPLATE_CC = 'ops/form/cc.phtml';

    /**
     *  CC Redirect Payment Template
     */
    const FRONTEND_TEMPLATE_CC_REDIRECT = 'ops/form/ccRedirect.phtml';

    /**
     * @return string
     */
    public function getSaveCcBrandUrl()
    {
        return Mage::getModel('ops/config')->getSaveCcBrandUrl();
    }

    /**
     * @param null $storeId
     * @param bool $admin
     *
     * @return mixed
     */
    public function getCcSaveAliasUrl($storeId = null, $admin = false)
    {
        return Mage::getModel('ops/config')->getCcSaveAliasUrl($storeId, $admin);
    }

    /**
     * checks if the 'alias' payment method (!) is available
     * no check for customer has aliases here
     * just a passthrough of the isAvailable of Netresearch_OPS_Model_Payment_Abstract::isAvailable
     *
     * @return boolean
     */
    public function isAliasPMEnabled()
    {
        return Mage::getModel('ops/config')->isAliasManagerEnabled($this->getMethodCode());
    }

    /**
     * Check if there are any actual aliases that will be displayed in checkout.
     *
     * @return bool
     */
    public function isAliasAvailable()
    {
        if (!$this->isAliasPMEnabled() || !Mage::helper('customer/data')->isLoggedIn()) {
            return false;
        }

        $quote = $this->getQuote();
        /** @var Netresearch_OPS_Helper_Alias $aliasManager */
        $aliasManager = Mage::helper('ops/alias');
        $aliasCount = $aliasManager->getAliasesForAddresses(
            $quote->getCustomer()->getId(),
            $quote->getBillingAddress(),
            $quote->getShippingAddress(),
            $quote->getStoreId()
        )
                                   ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::ACTIVE)
                                   ->addFieldToFilter('payment_method', $this->getMethod()->getCode())
                                   ->count();

        return $aliasCount > 0;
    }

    /**
     * determines whether the alias hint is shown to guests or not
     *
     * @return bool true if alias feature is enabled and display the hint to
     * guests is enabled
     */
    public function isAliasInfoBlockEnabled()
    {
        return ($this->isAliasPMEnabled()
                && Mage::getModel('ops/config')->isAliasInfoBlockEnabled());
    }

    /**
     * @return string[]
     */
    public function getCcBrands()
    {
        return explode(',', $this->getConfig()->getAcceptedCcTypes($this->getMethodCode()));
    }

    public function checkIfBrandHasAliasInterfaceSupport($alias)
    {
        $brand = $this->getStoredAliasBrand($alias);
        $allowedBrands = $this->getMethod()->getBrandsForAliasInterface();

        return in_array($brand, $allowedBrands);
    }

    /**
     * Get template based on method code i.e. ops_cc or ops_cc_redirect.
     *
     * @return string
     */
    public function getTemplate()
    {
        if ($this->getMethodCode() === Netresearch_OPS_Model_Payment_CcRedirect::CODE
            || $this->getMethodCode() === Netresearch_OPS_Model_Payment_DebitcardRedirect::CODE
        ) {
            return self::FRONTEND_TEMPLATE_CC_REDIRECT;
        }

        return self::FRONTEND_TEMPLATE_CC;
    }

    /**
     * @return bool
     */
    public function isAliasEnabled()
    {
        $storeId = $this->getQuote()->getStore()->getId();

        return $this->getConfig()->isAliasEnabled($storeId);
    }

    /**
     * @return bool
     */
    public function resetAliasSuccess()
    {
        return $this->getQuote()->getData('resetAlias') ? true : false;
    }

    /**
     * @return string
     * @throws Zend_Controller_Request_Exception
     */
    public function getAcceptHeader()
    {
        $acceptHeader = $this->getRequest()->getHeader('Accept') ?: '*/*';

        return str_replace('text/javascript, ', '', $acceptHeader);
    }
}
