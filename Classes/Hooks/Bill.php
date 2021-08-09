<?php

namespace JambageCom\TcpdfbillTtProducts\Hooks;

/**
* This file is part of the TYPO3 CMS project.
*
* It is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License, either version 2
* of the License, or any later version.
*
* For the full copyright and license information, please read the
* LICENSE.txt file that was distributed with this source code.
*
* The TYPO3 project - inspiring people to share!
*/

/**
* Bill class to generate a PDF bill for tt_products
*
* USE:
* The class is intended to be used as a hook for tt_products.
* $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['tt_products']['billdelivery'][] =
*    'JambageCom\\TcpdfbillTtProducts\\Hooks\\Bill';
* @author Franz Holzinger <franz@ttproducts.de>
*/


use TYPO3\CMS\Core\Utility\GeneralUtility;


class Bill implements \TYPO3\CMS\Core\SingletonInterface {

    public $LOCAL_LANG = [];		// Local Language content
    public $extensionKey = TCPDFBILL_TT_PRODUCTS_EXT;

    public function generateBill (
        $pObj,
        $cObj,
        $templateCode,
        array $mainMarkerArray,
        array $itemArray,
        array $calculatedArray,
        array $orderArray,
        array $basketExtra,
        array $basketRecs,
        $type,
        array $generationConf,
        &$result
    ) {
        $orderUid = 0;
        $result = false;
        $publicPath = \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/';

        if (isset($orderArray['uid'])) {
            $orderUid = intval($orderArray['uid']);
        } else if (isset($orderArray['orderUid'])) {
            $orderUid = intval($orderArray['orderUid']);
        }

        if($orderUid) {
            $errorCode = [];
            $basket1 = GeneralUtility::makeInstance('tx_ttproducts_basket');
            $basketView = GeneralUtility::makeInstance('tx_ttproducts_basket_view');
            $infoViewObj = GeneralUtility::makeInstance('tx_ttproducts_info_view');
            $subpartMarker = 'TCPDF_BILL_PDF_TEMPLATE';
            $localConf = [];
            if (isset($generationConf['conf.'])) {
                $localConf = $generationConf['conf.'];
            }

            $this->fullURL	= GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
            $languageObj = GeneralUtility::makeInstance(\JambageCom\TcpdfbillTtProducts\Api\Localization::class);
            $languageObj->init(
                $this->extensionKey,
                $localConf['_LOCAL_LANG.'],
                DIV2007_LANGUAGE_SUBPATH
            );

            $functionResult = $languageObj->loadLocalLang(
                'EXT:' . $this->extensionKey . DIV2007_LANGUAGE_SUBPATH . 'locallang.xml',
                false
            );
            
            if (!$functionResult) {
                return false;
            }

            if (!isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TCPDFBILL_TT_PRODUCTS_EXT]['libraryPath'])) {
                $extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                    \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
                )->get(TCPDFBILL_TT_PRODUCTS_EXT);
               $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TCPDFBILL_TT_PRODUCTS_EXT]['libraryPath'] = $publicPath . $extensionConfiguration['libraryPath'] . '/';
            }

            $theFilename = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][TCPDFBILL_TT_PRODUCTS_EXT]['libraryPath'] . 'tcpdf.php';

            if (!file_exists($theFilename)) {
                debug($theFilename, 'ERROR in extension ' . TCPDFBILL_TT_PRODUCTS_EXT . ': TCPDF file ' . $theFilename . ' does not exist! You must set the appropriate libraryPath in the Settings Module -> Extension Configuration.'); // keep this
                return false;
            }

            $this->LOCAL_LANG = $languageObj->getLocalLang();
            $LLkey = $languageObj->getLanguage();

            if (
                $templateCode == '' ||
                !strpos($templateCode, '###' . $subpartMarker . '###') ||
                isset($localConf['templateFile'])
            ) {
                $templateFile = $localConf['templateFile'] ? $localConf['templateFile'] : 'EXT:' . $this->extensionKey . '/Resources/Private/pdf_template.html';
                $templateCode = 
                    \JambageCom\Div2007\Utility\FrontendUtility::fileResource($templateFile);
            }

            $subpartArray = $linkpartArray = [];
            $markerArray = $mainMarkerArray;

            $billMarkerArray['###ORDER_DATE###'] = date('d.m.Y', time());
            $billMarkerArray['###ORDER_UID###']  = $orderUid;
            $billMarkerArray['###ORDER_BEMERKUNG###'] = htmlspecialchars($basketRecs['delivery']['note']);
            if (!isset($markerArray['###ORDER_BILL_NO###'])) {
                $billMarkerArray['###ORDER_BILL_NO###']  = $orderUid;
            }

            $configurations_link['parameter'] = $basket1->conf['PIDagb'];
            $configurations_link['returnLast'] = $url;
            $url  = $cObj->typolink(null, $configurations_link);
            $billMarkerArray['###AGB_LINK###'] = $this->fullURL . $url;
            $billMarkerArray['###SERVER###'] = $this->fullURL;
            $translationArray = $this->LOCAL_LANG['default'];
            if (isset($this->LOCAL_LANG[$LLkey])) {
                $translationArray = $this->LOCAL_LANG[$LLkey];
            }

            foreach ($translationArray as $key => $translationPart) {
                $billMarkerArray['###' . strtoupper($key) . '###'] = $translationPart[0]['target'];
            }
            $billHtml = 'ERROR: Wrong version of tt_products';

            $eInfo = \JambageCom\Div2007\Utility\ExtensionUtility::getExtensionInfo(TT_PRODUCTS_EXT);

            if (is_array($eInfo)) {
                $ttProductsVersion = $eInfo['version'];

                if (
                    version_compare($ttProductsVersion, '2.7.0', '>=') &&
                    version_compare($ttProductsVersion, '2.8.0', '<')
                ) {
                // this is not supported in tt_products 2.7.30
                    $billHtml =
                        $basketView->getView(
                            $templateCode,
                            'EMAIL',
                            $infoViewObj,
                            false,
                            false,
                            true,
                            '###' . $subpartMarker . '###',
                            $markerArray
                        );
                } else if (
                    version_compare($ttProductsVersion, '2.8.0', '>=') &&
                    version_compare($ttProductsVersion, '2.9.0', '<')
                ) {
                    $billHtml =
                        $basketView->getView(
                            $templateCode,
                            'EMAIL',
                            $infoViewObj,
                            false,
                            false,
                            true,
                            $subpartMarker,
                            $markerArray,
                            ''
                        );
                } else if (
                    version_compare($ttProductsVersion, '2.9.1', '>=') &&
                    version_compare($ttProductsVersion, '2.9.11', '<')
                ) {
                    $billHtml =
                        $basketView->getView(
                            $templateCode,
                            'EMAIL',
                            $infoViewObj,
                            false,
                            false,
                            $calculatedArray,
                            true,
                            $subpartMarker,
                            $markerArray,
                            '',
                            $itemArray,
                            $orderArray,
                            $basketExtra
                        );
                } else if (
                    version_compare($ttProductsVersion, '2.11.0', '>=') &&
                    version_compare($ttProductsVersion, '2.12.0', '<')
                ) {
                    $basket1 = GeneralUtility::getUserObj('&tx_ttproducts_basket');
                    $basketView = GeneralUtility::getUserObj('&tx_ttproducts_basket_view');
                    $infoViewObj = GeneralUtility::getUserObj('&tx_ttproducts_info_view');

                    $multiOrderArray = [];
                    $multiOrderArray['0'] = $orderArray;

                    $billHtml =
                        $basketView->getView(
                            $templateCode,
                            'EMAIL',
                            $infoViewObj,
                            false,
                            false,
                            $calculatedArray,
                            true,
                            $subpartMarker,
                            $markerArray,
                            '',
                            $itemArray,
                            $multiOrderArray,
                            [],
                            $basketExtra
                        );
                } else if (
                    version_compare($ttProductsVersion, '2.9.11', '>=') &&
                    version_compare($ttProductsVersion, '2.10.0', '<') ||

                    version_compare($ttProductsVersion, '2.12.0', '>=') &&
                    version_compare($ttProductsVersion, '3.0.0', '<')
                ) {
                    $multiOrderArray = [];
                    $multiOrderArray['0'] = $orderArray;
                    $billHtml =
                        $basketView->getView(
                            $errorCode,
                            $templateCode,
                            'EMAIL',
                            $infoViewObj,
                            false,
                            false,
                            $calculatedArray,
                            true,
                            $subpartMarker,
                            $markerArray,
                            '',
                            $itemArray,
                            false,
                            $multiOrderArray,
                            [],
                            $basketExtra,
                            $basketRecs
                        );
                } else if (
                    version_compare($ttProductsVersion, '3.0.0', '>=') &&
                    version_compare($ttProductsVersion, '3.3.0', '<')
                ) {
                    $multiOrderArray = [];
                    $multiOrderArray['0'] = $orderArray;

                    $billHtml =
                        $basketView->getView(
                            $errorCode,
                            $templateCode,
                            'EMAIL',
                            $infoViewObj,
                            false,
                            false,
                            $calculatedArray,
                            true,
                            $subpartMarker,
                            $markerArray,
                            [],
                            [],
                            '',
                            $itemArray,
                            false,
                            $multiOrderArray,
                            [],
                            $basketExtra,
                            $basketRecs
                        );
                }
            }

            if (empty($errorCode)) {
                $templateService = 
                    GeneralUtility::makeInstance(
                        \TYPO3\CMS\Core\Service\MarkerBasedTemplateService::class
                    );
                $billHtml =
                    $templateService->substituteMarkerArrayCached(
                        $billHtml,
                        $billMarkerArray,
                        $subpartArray
                    );
            } else {
                $billHtml = '';
                $message = \JambageCom\Div2007\Utility\ErrorUtility::getMessage($languageObj, $errorCode);
                GeneralUtility::sysLog(
                    $message,
                    $this->extensionKey,
                    GeneralUtility::SYSLOG_SEVERITY_ERROR
                );
            }

            if ($billHtml == '') {
                return false;
            }

            $pdf_body = trim($billHtml);
            $path = 'typo3temp/';

            if (isset($localConf['path'])) {
                $path = $localConf['path'] . '/';
            }

            $pdfFile = $path . 'Order-' . $orderUid . '.pdf';

            require_once($theFilename);
            $pdf =
                GeneralUtility::makeInstance(
                    'TCPDF',
                    PDF_PAGE_ORIENTATION,
                    PDF_UNIT,
                    PDF_PAGE_FORMAT,
                    true,
                    'UTF-8',
                    false
                );

            $pdf->SetPrintHeader(false);
            $pdf->setPrintFooter(false);

            $l = [];

            // PAGE META DESCRIPTORS --------------------------------------

            $l['a_meta_charset'] = 'UTF-8';
            $l['a_meta_dir'] = 'ltr';
            $l['a_meta_language'] = 'de';

            // TRANSLATIONS --------------------------------------
            $l['w_page'] = 'Seite';

            if (
                isset($localConf['_LOCAL_LANG.']) &&
                isset($localConf['_LOCAL_LANG.'][$LLkey . '.'])
            ) {
                foreach ($localConf['_LOCAL_LANG.'][$LLkey . '.'] as $key => $value) {
                    $l[$key] = strip_tags($value);
                }
            }

            //==============
            $pdf->setLanguageArray($l);


            $font = [];
            $font['family'] = 'freesans';
            $font['style'] = '';
            $font['size'] = 12;
            if (
                isset($localConf['font.'])
            ) {
                foreach ($localConf['font.'] as $key => $value) {
                    $font[$key] = strip_tags($value);
                }
            }

            $pdf->SetFont($font['family'], $font['style'], $font['size'], '', 'false');
            $pdf->AddPage();
            $pdf->writeHTML($pdf_body);
            ob_clean();
			$pdf->Output($publicPath . $pdfFile, false);
            $result = $publicPath . $pdfFile;
        }

        return $result;
    }
}

