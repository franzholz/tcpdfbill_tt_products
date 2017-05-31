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

class Bill {

    public $LOCAL_LANG = array();		// Local Language content

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

        if (isset($orderArray['uid'])) {
            $orderUid = intval($orderArray['uid']);
        } else if (isset($orderArray['orderUid'])) {
            $orderUid = intval($orderArray['orderUid']);
        }

        if($orderUid) {

            $errorCode = array();
            $basket1 = GeneralUtility::getUserObj('tx_ttproducts_basket');
            $basketView = GeneralUtility::getUserObj('tx_ttproducts_basket_view');
            $infoViewObj = GeneralUtility::getUserObj('tx_ttproducts_info_view');
            $subpartMarker = 'TCPDF_BILL_PDF_TEMPLATE';
            $conf = array();
            if (isset($generationConf['conf.'])) {
                $conf = $generationConf['conf.'];
            }

            $this->fullURL	= GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
            $langObj = GeneralUtility::makeInstance('JambageCom\\TcpdfbillTtProducts\\Language\\Language');
            $langObj->init1(
                $pObj,
                $cObj,
                $conf,
                'Classes/Hooks/Bill.php'
            );

            $LLkey = $langObj->getLanguage();
            $languageFile = 'EXT:tcpdfbill_tt_products/Resources/Private/locallang.xml';

            if (version_compare(TYPO3_version, '7.4.0', '>')) {

                $languageFactory = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Localization\\LocalizationFactory');
                $this->LOCAL_LANG =
                    $languageFactory->getParsedData(
                        $languageFile,
                        $LLkey,
                        'utf-8'
                    );
            } else {
                $this->LOCAL_LANG = GeneralUtility::readLLfile($languageFile, $LLkey);
            }

            if (
                $templateCode == '' ||
                !strpos($templateCode, '###' . $subpartMarker . '###') ||
                isset($conf['templateFile'])
            ) {
                $templateFile = $conf['templateFile'] ? $conf['templateFile'] : 'EXT:tcpdfbill_tt_products/Resources/Private/pdf_template.html';
                $templateCode = $cObj->fileResource($templateFile);
            }

            $subpartArray = $linkpartArray = array();
            $markerArray = $mainMarkerArray;

            $billMarkerArray['###ORDER_DATE###'] = date('d.m.Y', time());
            $billMarkerArray['###ORDER_UID###']  = $orderUid;
            $billMarkerArray['###ORDER_BEMERKUNG###'] = htmlspecialchars($basketRecs['delivery']['note']);
            if (!isset($markerArray['###ORDER_BILL_NO###'])) {
                $billMarkerArray['###ORDER_BILL_NO###']  = $orderUid;
            }

            $contentItem = "";

            $configurations_link['parameter'] = $basket1->conf['PIDagb'];
            $configurations_link['returnLast'] = url;
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

            $eInfo = \tx_div2007_alpha5::getExtensionInfo_fh003(TT_PRODUCTS_EXT);

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
                    version_compare($ttProductsVersion, '2.10.0', '<')
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

                    $multiOrderArray = array();
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
                            array(),
                            $basketExtra
                        );
                } else if (
                    version_compare($ttProductsVersion, '2.12.0', '>=') &&
                    version_compare($ttProductsVersion, '3.1.0', '<')
                ) {
                    $multiOrderArray = array();
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
                            $multiOrderArray,
                            array(),
                            $basketExtra,
                            $basketRecs
                        );
                }
            }

            if (empty($errorCode)) {
                $billHtml =
                    $cObj->substituteMarkerArrayCached(
                        $billHtml,
                        $billMarkerArray,
                        $subpartArray
                    );
            } else {
                $billHtml = tx_div2007_error::getMessage($langObj, $errorCode);
            }

            if ($billHtml == '') {
                return false;
            }

            $pdf_body = trim($billHtml);
            $path = 'typo3temp/';

            if (isset($conf['path'])) {
                $path = $conf['path'] . '/';
            }

            $pdfFile = $path . 'Order-' . $orderUid . '.pdf';
            require_once(TCPDFBILL_TT_PRODUCTS_LIBRARYPATH . 'tcpdf.php');
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

            $l = array();

            // PAGE META DESCRIPTORS --------------------------------------

            $l['a_meta_charset'] = 'UTF-8';
            $l['a_meta_dir'] = 'ltr';
            $l['a_meta_language'] = 'de';

            // TRANSLATIONS --------------------------------------
            $l['w_page'] = 'Seite';

            if (
                isset($conf['_LOCAL_LANG.']) &&
                isset($conf['_LOCAL_LANG.'][$LLkey . '.'])
            ) {
                foreach ($conf['_LOCAL_LANG.'][$LLkey . '.'] as $key => $value) {
                    $l[$key] = strip_tags($value);
                }
            }

            //==============
            $pdf->setLanguageArray($l);

            $font = array();
            $font['family'] = 'freesans';
            $font['style'] = '';
            $font['size'] = 12;

            if (
                isset($conf['font.'])
            ) {
                foreach ($conf['font.'] as $key => $value) {
                    $font[$key] = strip_tags($value);
                }
            }

            $pdf->SetFont($font['family'], $font['style'], $font['size'], '', 'false');
            $pdf->AddPage();
            $pdf->writeHTML($pdf_body);
            ob_clean();
            $pdf->Output(PATH_site . $pdfFile, false);

            $result = PATH_site . $pdfFile;
        }

        return $result;
    }
}

