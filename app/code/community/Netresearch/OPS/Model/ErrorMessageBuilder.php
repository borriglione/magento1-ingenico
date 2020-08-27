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
 * ErrorMessageBuilder.php
 *
 * @package  Netresearch_OPS
 * @author   Andreas Müller <andreas.mueller@netresearch.de>
 */
class Netresearch_OPS_Model_ErrorMessageBuilder
{
    const DEFAULT_CODE = 'Default';

    /**
     * Generate Error Message
     *
     * @param array $params
     * @param $storeId
     * @return string
     */
    public function generateErrorMessage(array $params, $storeId = null)
    {
        $message = '';

        /** @var Netresearch_OPS_Model_Config $opsConfig */
        $opsConfig = Mage::getSingleton('ops/config');

        foreach ($params as $error) {
            if ($opsConfig->getErrorMessage($error, $storeId)) {
                $message .= $opsConfig->getErrorMessage($error, $storeId) . ' ';
            } else {
                $message .= $opsConfig->getErrorMessage(self::DEFAULT_CODE, $storeId);
            }
        }

        return $message;
    }
}
