<?php
/**
 * Netresearch_Epayments
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
 * @category  Epayments
 * @package   Netresearch_Epayments
 * @author    Paul Siedler <paul.siedler@netresearch.de>
 * @copyright 2018 Netresearch GmbH & Co. KG
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.netresearch.de/
 */

/**
 * Replace settings for scoped config if it is configured in the current scope
 *
 * @param $scopeType
 * @param $scopeId
 * @param $parentScopeType
 * @param $parentScopeId
 * @param bool $force
 */
$splitMethods = function (
    $scopeType,
    $scopeId,
    $parentScopeType,
    $parentScopeId,
    $force = false
) {
    $configPathInline = 'payment/%s/inline_types';
    $configPathRedirect = 'payment/%s/types';
    $methods = array('ops_cc', 'ops_dc');
    /** @var Mage_Core_Model_Config $config */
    $config = Mage::getConfig();

    foreach ($methods as $method) {
        $inlinePath = sprintf(
            $configPathInline,
            $method
        );
        if ($inlineTypes = $config->getNode(
            $inlinePath,
            $scopeType,
            $scopeId
        )) {
            $inlineTypes = $inlineTypes->asArray();
        }

        if ($parentInlineTypes = $config->getNode(
            $inlinePath,
            $parentScopeType,
            $parentScopeId
        )) {
            $parentInlineTypes = $parentInlineTypes->asArray();
        }

        $acceptedPath = sprintf(
            $configPathRedirect,
            $method
        );
        if ($acceptedTypes = $config->getNode(
            $acceptedPath,
            $scopeType,
            $scopeId
        )) {
            $acceptedTypes = $acceptedTypes->asArray();
        }

        if ($parentAcceptedTypes = $config->getNode(
            $acceptedPath,
            $parentScopeType,
            $parentScopeId
        )) {
            $parentAcceptedTypes->asArray();
        }

        if (($acceptedTypes === $parentAcceptedTypes && $inlineTypes === $parentInlineTypes && !$force)
            || ($acceptedTypes === '' && $inlineTypes === '')
        ) {
            // everything will be updated in the parent config
            continue;
        }

        if ($acceptedTypes && !is_array($acceptedTypes)) {
            $acceptedTypes = explode(',', $acceptedTypes);
        }

        if ($inlineTypes && !is_array($inlineTypes)) {
            $inlineTypes = explode(',', $inlineTypes);
        }

        $redirectTypes = array_diff(
            $acceptedTypes ?: array(),
            $inlineTypes ?: array()
        );

        $config->saveConfig(
            "payment/{$method}/types",
            implode(
                ',',
                $inlineTypes ?: array()
            ),
            $scopeType,
            $scopeId
        );
        $config->saveConfig(
            "payment/{$method}_redirect/types",
            implode(
                ',',
                $redirectTypes
            ),
            $scopeType,
            $scopeId
        );
    }
};

$websites = Mage::app()->getWebsites();

/** @var Mage_Core_Model_Website $website */
foreach ($websites as $website) {
    /** @var Mage_Core_Model_Store $store */
    foreach ($website->getStores() as $store) {
        $splitMethods('store', $store->getId(), 'website', $website->getId());
    }

    $splitMethods('website', $website->getId(), 'default', 0);
}

$splitMethods('default', 0, 'default', 0, true);

Mage::getConfig()->cleanCache();


