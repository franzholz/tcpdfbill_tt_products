<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "tcpdfbill_tt_products".
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'TCPDF bill for tt_products',
    'description' => 'tt_products Extension with automatic PDF bill generation using the TCPDF library. Works with tt_products 2.9.20-3.2.99',
    'category' => 'fe',
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author_company' => '',
    'version' => '0.3.0',
    'constraints' => [
        'depends' => [
            'php' => '5.5.0-7.99.99',
            'typo3' => '9.5.0-11.5.99',
			'div2007' => '1.10.27-0.0.0',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            't3_tcpdf' => '5.1.1-0.0.0',
            'tcpdf' => '3.0.0-0.0.0',
        ],
    ],
];

