<?php
defined('TYPO3_MODE') || die('Access denied.');
defined('TYPO3_version') || die('The constant TYPO3_version is undefined in tcpdfbill_tt_products!');

call_user_func(function () {
    if (!defined ('TCPDFBILL_TT_PRODUCTS_EXT')) {
        define('TCPDFBILL_TT_PRODUCTS_EXT', 'tcpdfbill_tt_products');
    }

    $extensionConfiguration = array();

    if (
        defined('TYPO3_version') &&
        version_compare(TYPO3_version, '9.0.0', '>=')
    ) {
        $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get(TCPDFBILL_TT_PRODUCTS_EXT);
    } else if (isset($_EXTCONF)) {
        $extensionConfiguration = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:
    }

    if (!defined ('TCPDFBILL_TT_PRODUCTS_LIBRARYPATH')) {
        define('TCPDFBILL_TT_PRODUCTS_LIBRARYPATH', PATH_site . $extensionConfiguration['libraryPath'] . '/');
    }

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_products']['billdelivery'][] =
    'JambageCom\\TcpdfbillTtProducts\\Hooks\\Bill';

});


