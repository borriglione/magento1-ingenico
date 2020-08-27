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
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @author    Andreas Mueller <andreas.mueller@netresearch.de>
 */

class Netresearch_OPS_Model_Payment_BankTransfer
    extends Netresearch_OPS_Model_Payment_Abstract
{
    const OPS_COLLECTOR_IBAN = 'collector_iban';
    const OPS_COLLECTOR_BIC = 'collector_bic';
    const OPS_PAYMENT_REFERENCE = 'payment_reference';

    /** Check if we can capture directly from the backend */
    protected $_canBackendDirectCapture = true;

    /** info source path */
    protected $_infoBlockType = 'ops/info_bankTransfer';

    /** form block type  */
    protected $_formBlockType = 'ops/form_bankTransfer';

    /** payment code */
    protected $_code = 'ops_bankTransfer';


    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        $countryId = '';
        if (is_object($data) && $data instanceof Varien_Object) {
            $countryId = $data->getCountryId();
        } elseif (is_array($data) && isset($data['country_id'])) {
            $countryId = $data['country_id'];
        }

        $pm = $brand = trim('Bank transfer' . (('*' == $countryId) ? '' : ' ' . $countryId));

        $payment = $this->getInfoInstance();
        $payment->setAdditionalInformation('PM', $pm);
        $payment->setAdditionalInformation('BRAND', $brand);

        parent::assignData($data);
        return $this;
    }

    /**
     * @return array
     */
    public static function getTransactionKeys()
    {
        return array(self::OPS_COLLECTOR_IBAN, self::OPS_COLLECTOR_BIC, self::OPS_PAYMENT_REFERENCE);
    }

}
