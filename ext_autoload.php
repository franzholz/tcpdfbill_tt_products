<?php


$key = 'tcpdfbill_tt_products';
$extensionPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($key, $script);

$result = array(
	'TCPDF' => TCPDFBILL_TT_PRODUCTS_LIBRARYPATH . 'tcpdf.php',
	'JambageCom\\TcpdfbillTtProducts\\Hooks\\Bill' => $extensionPath . 'Classes/Hooks/Bill.php',
	'JambageCom\\TcpdfbillTtProducts\\Language\\Language' => $extensionPath . 'Classes/Language/Language.php',
);

return $result;