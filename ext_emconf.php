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
    'shy' => '',
    'dependencies' => 'tt_products',
    'conflicts' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'author_company' => '',
    'version' => '0.0.6',
    'constraints' => array(
        'depends' => array(
            'php' => '5.3.0-7.99.99',
            'typo3' => '6.1.0-8.99.99',
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
);

