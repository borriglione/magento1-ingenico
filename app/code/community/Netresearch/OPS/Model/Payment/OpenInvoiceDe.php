<?php
/**
 * Netresearch_OPS_Model_Payment_OpenInvoiceDe
 *
 * @package
 * @copyright 2011 Netresearch
 * @author    Thomas Kappel <thomas.kappel@netresearch.de>
 * @license   OSL 3.0
 */
class Netresearch_OPS_Model_Payment_OpenInvoiceDe
    extends Netresearch_OPS_Model_Payment_OpenInvoice_Abstract
{
    /**
     * @var string
     */
    protected $pm = 'Open Invoice DE';

    /**
     * @var string
     */
    protected $brand = 'Open Invoice DE';

    /**
     * Whether we can capture directly from the backend
     *
     * @var bool
     */
    protected $_canBackendDirectCapture = false;

    /**
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * @var bool
     */
    protected $_canRefundInvoicePartial = false;

    /**
     * Info source path
     *
     * @var string
     */
    protected $_infoBlockType = 'ops/info_redirect';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'ops_openInvoiceDe';

    /**
     * Open Invoice DE is not available if quote has a coupon
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return boolean
     */
    public function isAvailable($quote=null)
    {
        /* availability depends on quote */
        if (false == $quote instanceof Mage_Sales_Model_Quote) {
            return false;
        }

        /* not available if quote contains a coupon and allow_discounted_carts is disabled */
        if (!$this->isAvailableForDiscountedCarts()
            && $quote->getSubtotal() != $quote->getSubtotalWithDiscount()
        ) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * @return string
     */
    public function getPaymentAction()
    {
        return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param null|array $requestParams
     * @return string[]
     */
    public function getMethodDependendFormFields($order, $requestParams=null)
    {
        $formFields = parent::getMethodDependendFormFields($order, $requestParams);

        $shippingAddress = $order->getShippingAddress();

        $gender = $order->getPayment()->getAdditionalInformation('gender');
        $gender = Mage::getSingleton('eav/config')
            ->getAttribute('customer', 'gender')
            ->getSource()
            ->getOptionText($gender);

        $formFields[ 'CIVILITY' ]               = $gender == 'Male' ? 'Herr' : 'Frau';
        $formFields[ 'ECOM_CONSUMER_GENDER' ]   = $gender == 'Male' ? 'M' : 'F';

        if (!$this->getConfig()->canSubmitExtraParameter($order->getStoreId())) {
            // add the shipto parameters even if the submitOption is false, because they are required for OpenInvoice
            $shipToParams = $this->getRequestHelper()->extractShipToParameters($shippingAddress, $order);
            $formFields   = array_merge($formFields, $shipToParams);
        }

        return $formFields;
    }

    /**
     * Getter for the allow_discounted_carts
     *
     * @return bool
     */
    protected function isAvailableForDiscountedCarts()
    {
        return (bool) $this->getConfigData('allow_discounted_carts');
    }
}
