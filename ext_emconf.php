<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "tcpdfbill_tt_products".
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
    'title' => 'TCPDF bill for tt_products',
    'description' => 'tt_products Extension with automatic PDF bill generation using the TCPDF library. Works with tt_products 2.8.1-3.1.99',
    'category' => 'fe',
    'author' => 'Franz Holzinger',
    'author_email' => 'franz@ttproducts.de',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author_company' => '',
    'version' => '0.1.1',
    'constraints' => array(
        'depends' => array(
            'php' => '5.3.0-7.99.99',
            'typo3' => '6.2.0-9.5.99',
			'div2007' => '1.10.27-0.0.0',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
);

