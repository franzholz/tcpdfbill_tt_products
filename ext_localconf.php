<?php
defined('TYPO3') || die('Access denied.');

call_user_func(function () {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_products']['billdelivery'][] =
    \JambageCom\TcpdfbillTtProducts\Hooks\Bill::class;

    // Add example configuration for the logging API
    $GLOBALS['TYPO3_CONF_VARS']['LOG']['JambageCom']['TcpdfbillTtProducts']['Hooks']['writerConfiguration'] = [
        // configuration for ERROR level log entries
        \TYPO3\CMS\Core\Log\LogLevel::ERROR => [
            // add a FileWriter
            \TYPO3\CMS\Core\Log\Writer\FileWriter::class => [
                // configuration for the writer
                'logFile' => \TYPO3\CMS\Core\Core\Environment::getVarPath() . '/log/tcpdfbill_tt_products.log'
            ]
        ]
    ];
});

