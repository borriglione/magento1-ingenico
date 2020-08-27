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
 * Netresearch_OPS_Model_Payment_Alias
 *
 * @category OPS
 * @package  Netresearch_OPS
 * @author   Andreas Müller <andreas.muellerr@netresearch.de>
 */
class Netresearch_OPS_Model_Payment_Alias extends Netresearch_OPS_Model_Payment_Abstract
{
    const CODE = 'ops_alias';

    protected $_code = self::CODE;

    /** form block type  */
    protected $_formBlockType = 'ops/form_alias';

    protected $_infoBlockType = 'ops/info_redirect';

    /**
     * if payment method is available
     *
     * @param Mage_Sales_Model_Quote $quote
     *
     * @return boolean
     */
    public function isAvailable($quote = null)
    {
        $result  = parent::isAvailable($quote);
        if ($result && $quote->getCustomerId()) {
            /** @var Netresearch_OPS_Model_Mysql4_Alias_Collection $aliasCollection */
            $aliasCollection = Mage::helper('ops/alias')->getAliasesForCustomer(
                $quote->getCustomer()->getId(),
                $quote
            );
            $aliasCount = $aliasCollection
                ->addFieldToFilter('state', Netresearch_OPS_Model_Alias_State::ACTIVE)
                ->setOrder('created_at', Varien_Data_Collection::SORT_ORDER_DESC)
                ->count();

            $result = $aliasCount > 0;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Redirect url to ops submit form
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        $this->getInfoInstance()->setMethodInstance(null);
        $actualInstance = $this->getInfoInstance()->getMethodInstance();
        return $actualInstance->getOrderPlaceRedirectUrl();
    }
}
