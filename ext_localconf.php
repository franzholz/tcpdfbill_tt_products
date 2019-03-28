<?php
defined('TYPO3_MODE') || die('Access denied.');

$_EXTCONF = unserialize($_EXTCONF);    // unserializing the configuration so we can use it here:

if (!defined ('TCPDFBILL_TT_PRODUCTS_LIBRARYPATH')) {
    define('TCPDFBILL_TT_PRODUCTS_LIBRARYPATH', PATH_site . $_EXTCONF['libraryPath'] . '/');
}

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_products']['billdelivery'][] =
'JambageCom\\TcpdfbillTtProducts\\Hooks\\Bill';

