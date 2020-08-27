<?php
/**
 * @category   OPS
 * @package    Netresearch_OPS
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Netresearch_OPS_Block_Form_Alias
 *
 * @author     Thomas Birke <thomas.birke@netresearch.de>
 * @author     Michael Lühr <michael.luehr@netresearch.de>
 * @copyright  Copyright (c) 2012 Netresearch GmbH & Co. KG
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Netresearch_OPS_Block_Form_Alias extends Netresearch_OPS_Block_Form
{
    const FRONTEND_TEMPLATE = 'ops/form/alias.phtml';

    protected $aliasDataForCustomer = array();

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate(self::FRONTEND_TEMPLATE);
    }


    /**
     * retrieves the alias data for the logged in customer
     *
     * @return array | null - array the alias data or null if the customer
     * is not logged in
     */
    public function getStoredAliasForCustomer()
    {
        if (Mage::helper('customer/data')->isLoggedIn()) {
            $quote = $this->getQuote();
            $aliases = Mage::helper('ops/alias')->getAliasesForAddresses(
                $quote->getCustomer()->getId(), $quote->getBillingAddress(),
                $quote->getShippingAddress(), $quote->getStoreId()
            )
                ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::ACTIVE)
                ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC);


            foreach ($aliases as $key => $alias) {
                $this->aliasDataForCustomer[$key] = $alias;
            }
        }

        return $this->aliasDataForCustomer;
    }

    /**
     * @param $alias
     * @return string
     */
    protected function getHumanReadableAlias($alias)
    {
        $helper = Mage::helper('ops');
        $aliasString = $helper->__('Credit Card Type') . ' ' . $helper->__($alias->getBrand());
        $aliasString .= ' ' . $helper->__('AccountNo') . ' ' . $helper->__($alias->getPseudoAccountOrCCNo());
        $aliasString .= ' ' . $helper->__('Expiration Date') . ' ' . $alias->getExpirationDate();
        return $aliasString;
    }

    /**
     * the brand of the stored card data
     *
     * @param $aliasId
     *
     * @return null|string - string if stored card data were found, null otherwise
     */
    public function getStoredAliasBrand($aliasId)
    {
        return $this->getStoredAliasDataForCustomer($aliasId, 'brand');
    }


    /**
     * gets all Alias CC brands
     *
     * @return array
     */
    public function getAliasBrands()
    {
        return Mage::getModel('ops/source_cc_aliasInterfaceEnabledTypes')
            ->getAliasInterfaceCompatibleTypes();
    }

    /**
     * @param $alias
     * @return mixed
     */
    public function getAliasMethod($alias)
    {
        return $aliasMethod = $alias->getData('payment_method');
    }

    /**
     * @param $aliasMethod
     * @return bool
     */
    public function isAliasCc($aliasMethod)
    {
        return $aliasMethod !== Netresearch_OPS_Model_Payment_DirectDebit::CODE;
    }

    /**
     * @return bool
     */
    public function isInlineCc($aliasMethod)
    {
        $result = true;
        if ($aliasMethod === Netresearch_OPS_Model_Payment_CcRedirect::CODE
            || $aliasMethod === Netresearch_OPS_Model_Payment_DebitcardRedirect::CODE
        ) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param $aliasId
     * @param $key
     * @return null|string
     */
    public function getExpirationDatePart($aliasId, $key)
    {
        $returnValue = null;
        $expirationDate = $this->getStoredAliasDataForCustomer($aliasId, 'expiration_date');
        // set expiration date to actual date if no stored Alias is used
        if ($expirationDate === null) {
            $expirationDate = date('my');
        }

        if (0 < strlen(trim($expirationDate))) {
            $expirationDateValues = str_split($expirationDate, 2);

            if ($key == 'month') {
                $returnValue = $expirationDateValues[0];
            }

            if ($key == 'year') {
                $returnValue = $expirationDateValues[1];
            }

            if ($key == 'complete') {
                $returnValue = implode('/', $expirationDateValues);
            }
        }

        return $returnValue;

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
