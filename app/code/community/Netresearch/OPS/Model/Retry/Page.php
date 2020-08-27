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
 * @category  OPS
 * @package   Netresearch_OPS
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2017 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */


class Netresearch_OPS_Model_Retry_Page
{
    const SHA_ALGORITHM = 'sha256';
    /**
     * @var Mage_Core_Model_Url
     */
    protected $urlBuilder;

    /**
     * @var Netresearch_OPS_Model_Config
     */
    protected $config;

    /**
     * @var Netresearch_OPS_Helper_Payment
     */
    protected $paymentHelper;

    /**
     * @var int
     */
    protected $storeId = null;

    /**
     * Page constructor.
     */
    public function __construct()
    {
        $this->config = Mage::getModel('ops/config');
        $this->urlBuilder = Mage::getModel('core/url');
        $this->paymentHelper = Mage::helper('ops/payment');
    }

    /**
     * Set store scope for retrieving retry page urls
     * @param $storeId
     *
     * @return $this
     */
    public function setStore($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Generates RetryPage url for the given OrderId
     * @param $opsOrderId
     * @param $storeId
     *
     * @return string
     */
    public function getRetryUrl($opsOrderId, $storeId = null)
    {
        if ($storeId !== null) {
            $this->setStore($storeId);
        }

        $queryParams = array(
            'orderID' => $opsOrderId,
            'SHASIGN' => $this->getShaSign($opsOrderId)
        );

        return $this->urlBuilder->getUrl(
            Netresearch_OPS_Model_Config::OPS_CONTROLLER_ROUTE_PAYMENT . 'retry',
            array(
                '_secure' => true,
                '_nosid' => true,
                '_query' => $queryParams,
                '_store' => $this->storeId
            )
        );
    }

    /**
     * Generates SHASign for given OrderId
     *
     * @param $opsOrderId
     *
     * @return string
     */
    protected function getShaSign($opsOrderId)
    {
        $shaOutCode = $this->config->getShaOutCode($this->storeId);
        $shaSet = $this->paymentHelper->getSHAInSet(
            array('orderID' => $opsOrderId),
            $shaOutCode
        );
        return strtoupper(
            hash(
                self::SHA_ALGORITHM,
                $shaSet
            )
        );
    }

    /**
     * Validates the SHA signature for an incoming request to the retry page
     *
     * @param $opsOrderId
     * @param $shaSign
     *
     * @return bool
     */
    public function validateShaSign($opsOrderId, $shaSign)
    {
        return $this->getShaSign($opsOrderId) == $shaSign;
    }
}