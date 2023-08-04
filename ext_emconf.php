<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "tcpdfbill_tt_products".
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'TCPDF bill for tt_products',
    'description' => 'tt_products Extension with automatic PDF bill generation using the TCPDF library. Works with tt_products 2.14.3-3.4.99',
    'category' => 'fe',
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author_company' => '',
    'version' => '0.3.1',
    'constraints' => [
        'depends' => [
            'php' => '7.4.0-8.99.99',
            'typo3' => '10.4.0-12.4.99',
			'div2007' => '1.12.0-0.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            't3_tcpdf' => '5.1.1-0.0.0',
            'tcpdf' => '3.0.0-0.0.0',
        ],
    ],
];

