<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(function () {
    if (!defined ('TCPDFBILL_TT_PRODUCTS_EXT')) {
        define('TCPDFBILL_TT_PRODUCTS_EXT', 'tcpdfbill_tt_products');
    }

    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_products']['billdelivery'][] =
    'JambageCom\\TcpdfbillTtProducts\\Hooks\\Bill';
});


