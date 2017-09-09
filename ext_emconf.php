<?php

########################################################################
# Extension Manager/Repository config file for ext: "user_ttproducts_test"
#
# Auto generated 14-07-2009 11:35
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'TCPDF bill for tt_products',
	'description' => 'tt_products Extension with automatic PDF bill generation using the TCPDF library. Works with tt_products 2.8.1-3.0.99',
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
	'version' => '0.0.4',
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
