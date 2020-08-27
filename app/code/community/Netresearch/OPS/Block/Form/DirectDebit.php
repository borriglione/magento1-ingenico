<?php

/**
 * Netresearch_OPS_Block_Form_DirectDebit
 *
 * @package   OPS
 * @copyright 2012 Netresearch App Factory AG <http://www.netresearch.de>
 * @author    Thomas Birke <thomas.birke@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Block_Form_DirectDebit extends Netresearch_OPS_Block_Form
{

    /**
     * Backend Payment Template
     */
    const TEMPLATE = 'ops/form/directDebit.phtml';

    /**
     * @var Mage_Directory_Model_Country[]
     */
    protected $countryDirectory;

    protected function _construct()
    {
        parent::_construct();
        $this->countryDirectory = Mage::getModel('directory/country')->getCollection()->addFieldToFilter(
            'country_id',
            array(
                'in' => $this->getDirectDebitCountryIds(),
            )
        )->getItems();
        $this->setTemplate(self::TEMPLATE);
    }

    /**
     * get ids of supported countries
     *
     * @return array
     */
    public function getDirectDebitCountryIds()
    {
        return explode(',', $this->getConfig()->getDirectDebitCountryIds());
    }

    /**
     * @return string
     */
    public function getSelectedCountryId()
    {
        $countryId = $this->getQuote()->getPayment()->getAdditionalInformation('country_id');
        if (Mage::app()->getStore()->isAdmin()) {
            $data = $this->getQuote()->getPayment()->getData('ops_directDebit_data');
            $countryId = $data && array_key_exists('country_id', $data) ? $data['country_id'] : '';
        }

        return $countryId;
    }

    /**
     * @return string
     */
    public function getSelectedBillingCountryId()
    {
        return $this->getQuote()->getBillingAddress()->getCountryId();
    }

    public function getCountryName($countryId)
    {
        if (isset($this->countryDirectory[$countryId])) {
            return $this->countryDirectory[$countryId]->getName();
        }

        return $countryId;
    }
}
