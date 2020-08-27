<?php
/**
 * See LICENSE.md for license details.
 */

class Netresearch_OPS_Model_Request_ThreeDsParameterBuilder
{
    /**
     * Challenge window size options available
     */
    const CHALLENGE_WINDOW_SIZE_250_400 = '01';
    const CHALLENGE_WINDOW_SIZE_390_400 = '02';
    const CHALLENGE_WINDOW_SIZE_500_600 = '03';
    const CHALLENGE_WINDOW_SIZE_600_400 = '04';
    const CHALLENGE_WINDOW_SIZE_FULL = '05';

    const BROWSER_USER_AGENT = 'browserUserAgent';
    const BROWSER_ACCEPT_HEADER = 'browserAcceptHeader';
    const MPI_CHALLENGE_WINDOW_SIZE = 'Mpi.challengeWindowSize';
    const FLAG_3_D = 'FLAG3D';
    const WIN_3_DS = 'WIN3DS';
    const LANGUAGE = 'LANGUAGE';
    const HTTP_ACCEPT = 'HTTP_ACCEPT';
    const HTTP_USER_AGENT = 'HTTP_USER_AGENT';
    const ACCEPTURL = 'ACCEPTURL';
    const DECLINEURL = 'DECLINEURL';
    const EXCEPTIONURL = 'EXCEPTIONURL';

    /**
     * @var Netresearch_OPS_Model_Config
     */
    protected $config;

    /**
     * @var Mage_Core_Controller_Request_Http
     */
    protected $request;

    /**
     * @var Mage_Core_Model_Locale
     */
    protected $locale;

    /**
     * ThreeDsParameterBuilder constructor.
     */
    public function __construct()
    {
        $this->request = Mage::app()->getRequest();
        $this->config = Mage::getModel('ops/config');
        $this->locale = Mage::app()->getLocale();
    }

    public function getParameters(Mage_Sales_Model_Quote $order)
    {
        $requestParamsThreeds = array();
        $methodCode = $order->getPayment()->getMethod();
        if ($this->config->get3dSecureIsActive($methodCode, $order->getStoreId())) {
            // read data transmitted from frontend
            $frontEndData = \json_decode($order->getPayment()->getAdditionalInformation('three_ds'), true) ?: array();
            foreach ($frontEndData as $key => $value) {
                $requestParamsThreeds[$key] = $value;
            }

            $requestParamsThreeds[self::BROWSER_USER_AGENT] = $this->request->getHeader('User-Agent');
            $requestParamsThreeds[self::MPI_CHALLENGE_WINDOW_SIZE] = self::CHALLENGE_WINDOW_SIZE_FULL;

            $requestParamsThreeds = array_merge(
                $requestParamsThreeds,
                array(
                    self::FLAG_3_D => 'Y',
                    self::WIN_3_DS => Netresearch_OPS_Model_Payment_Abstract::OPS_DIRECTLINK_WIN3DS,
                    self::LANGUAGE => str_replace('-', '_', $this->locale->getLocaleCode()),
                    self::HTTP_ACCEPT => substr($requestParamsThreeds[self::BROWSER_ACCEPT_HEADER], 0, 500) ?: '',
                    self::HTTP_USER_AGENT => substr($requestParamsThreeds[self::BROWSER_USER_AGENT], 0, 255) ?: '',
                    self::ACCEPTURL => $this->config->getAcceptUrl(),
                    self::DECLINEURL => $this->config->getDeclineUrl(),
                    self::EXCEPTIONURL => $this->config->getExceptionUrl(),
                )
            );
        }

        return $requestParamsThreeds;
    }

}
