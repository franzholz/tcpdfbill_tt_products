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

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Service\MarkerBasedTemplateService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

use JambageCom\Div2007\Utility\ErrorUtility;
use JambageCom\Div2007\Utility\ExtensionUtility;
use JambageCom\Div2007\Utility\FrontendUtility;

use JambageCom\TcpdfbillTtProducts\Api\Localization;


class Bill implements SingletonInterface,  LoggerAwareInterface
{
    use LoggerAwareTrait;

	const BODY = 1;
	const HEADER = 2;
	const FOOTER = 3;

    public $LOCAL_LANG = [];		// Local Language content
    public $extensionKey = 'tcpdfbill_tt_products';
    private $typeArray = [
        Bill::BODY   => 'body',
        Bill::HEADER => 'header',
        Bill::FOOTER => 'footer' 
    ];

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
    )
    {
        $orderUid = 0;
        $result = false;
        $publicPath = Environment::getPublicPath() . '/';
        $useHeaderTemplate = true;
        $useFooterTemplate = true;
        $templateArray = [
            BILL::BODY => $templateCode,
            BILL::HEADER => '',
            BILL::FOOTER => ''
        ];
        $htmlParts = [
            BILL::BODY => '',
            BILL::HEADER => '',
            BILL::FOOTER => ''
        ];        
        $multiOrderArray = [];
        $multiOrderArray['0'] = $orderArray;

        if (isset($orderArray['uid'])) {
            $orderUid = intval($orderArray['uid']);
        } else if (isset($orderArray['orderUid'])) {
            $orderUid = intval($orderArray['orderUid']);
        }

        if($orderUid) {
            $templateService = 
                GeneralUtility::makeInstance(
                    MarkerBasedTemplateService::class
                );
            $errorCode = [];
            $basket1 = GeneralUtility::makeInstance('tx_ttproducts_basket');
            $basketView = GeneralUtility::makeInstance('tx_ttproducts_basket_view');
            $infoViewObj = GeneralUtility::makeInstance('tx_ttproducts_info_view');
            $subpartMarker = 'TCPDF_BILL_PDF_TEMPLATE';
            $languageSubpath = '/Resources/Private/Language/';
            $localConf = [];
            if (isset($generationConf['conf.'])) {
                $localConf = $generationConf['conf.'];
            }

            $this->fullURL	= GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
            $languageObj = GeneralUtility::makeInstance(Localization::class);
            $languageObj->init(
                $this->extensionKey,
                $localConf['_LOCAL_LANG.'] ?? '',
                $languageSubpath
            );

            $functionResult = $languageObj->loadLocalLang(
                'EXT:' . $this->extensionKey . $languageSubpath . 'locallang.xlf',
                false
            );
            
            if (!$functionResult) {
                return false;
            }

            if (
                !isset($GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extensionKey]['libraryPath'])
            ) {
                $extensionConfiguration = GeneralUtility::makeInstance(
                    ExtensionConfiguration::class
                )->get($this->extensionKey);
               $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extensionKey]['libraryPath'] = $publicPath . $extensionConfiguration['libraryPath'] . '/';
            }

            $theFilename = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extensionKey]['libraryPath'] . 'tcpdf.php';

            if (!file_exists($theFilename)) {
                debug($theFilename, 'ERROR in extension ' . $this->extensionKey . ': TCPDF file ' . $theFilename . ' does not exist! You must set the appropriate libraryPath in the Settings Module -> Extension Configuration.'); // keep this
                return false;
            }

            $this->LOCAL_LANG = $languageObj->getLocalLang();
            $LLkey = $languageObj->getLanguage();

            if (
                $templateCode == '' ||
                !strpos($templateCode, '###' . $subpartMarker . '###') ||
                (
                    isset($localConf['templateFile']) &&
                    !isset($localConf['templateFile.'][BILL::BODY])
                )
            ) {
                $bodyFile = '';
                if (isset($localConf['templateFile'])) {
                    $bodyFile = $localConf['templateFile'];
                }
                $templateFile = ($bodyFile ? $bodyFile : 'EXT:' . $this->extensionKey . '/Resources/Private/body_template.html');
                $templateArray[BILL::BODY] =  FrontendUtility::fileResource($templateFile);
            }

            if (isset($localConf['templateFile.'])) {
                foreach($templateArray as $type => $html) {
                    switch ($type) {
                        case BILL::BODY:
                        case BILL::HEADER:
                        case BILL::FOOTER:
                            $templateFile = (isset($localConf['templateFile.'][$this->typeArray[$type]]) ? $localConf['templateFile.'][$this->typeArray[$type]] : 'EXT:' . $this->extensionKey . '/Resources/Private/' . $this->typeArray[$type] . '_template.html');

                            $templateArray[$type] =  FrontendUtility::fileResource($templateFile);
                        default:
                            break;
                    }
                }
            }

            $subpartArray = $linkpartArray = [];
            $markerArray = $mainMarkerArray;

            $billMarkerArray['###ORDER_DATE###'] = date('d.m.Y', time());
            $billMarkerArray['###ORDER_UID###']  = $orderUid;
            $billMarkerArray['###ORDER_BEMERKUNG###'] = htmlspecialchars($basketRecs['delivery']['note']);
            if (!isset($markerArray['###ORDER_BILL_NO###'])) {
                $billMarkerArray['###ORDER_BILL_NO###']  = $orderUid;
            }

            $configurations_link = [];
            $configurations_link['parameter'] = $basket1->conf['PIDagb'];
            $configurations_link['returnLast'] = 'url';
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
            $eInfo = ExtensionUtility::getExtensionInfo('tt_products');

            if (is_array($eInfo)) {
                $ttProductsVersion = $eInfo['version'];

                if (
                    version_compare($ttProductsVersion, '2.9.11', '>=') &&
                    version_compare($ttProductsVersion, '2.10.0', '<') ||

                    version_compare($ttProductsVersion, '2.12.0', '>=') &&
                    version_compare($ttProductsVersion, '3.0.0', '<')
                ) {
                    $billHtml =
                        $basketView->getView(
                            $errorCode,
                            $templateArray[BILL::BODY],
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
                    version_compare($ttProductsVersion, '3.2.7', '<')
                ) {
                    $billHtml =
                        $basketView->getView(
                            $errorCode,
                            $templateArray[BILL::BODY],
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
                } else if (
                    version_compare($ttProductsVersion, '3.2.7', '>=') &&
                    version_compare($ttProductsVersion, '3.5.0', '<')
                ) {
                    $billHtml =
                        $basketView->getView(
                            $errorCode,
                            $templateArray[BILL::BODY],
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
                $billHtml =
                    $templateService->substituteMarkerArrayCached(
                        $billHtml,
                        $billMarkerArray,
                        $subpartArray
                    );
            } else {
                $billHtml = '';
                $message = ErrorUtility::getMessage($languageObj, $errorCode);
                $this->logger->error(
                    $message,
                    [$this->extensionKey]
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
            $theClass = 'TCPDF';
            if (
                !empty($templateArray[BILL::HEADER]) ||
                !empty($templateArray[BILL::FOOTER])
            ) {
                $theClass = TcpdfBill::class;
            }

            $pdf =
                GeneralUtility::makeInstance(
                    $theClass,
                    PDF_PAGE_ORIENTATION,
                    PDF_UNIT,
                    PDF_PAGE_FORMAT,
                    true,
                    'UTF-8',
                    false
                );

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
            // set margins
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            // set auto page breaks
            $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    
            if ($theClass == TcpdfBill::class) {
                $types = [BILL::HEADER, BILL::FOOTER];
                foreach ($types as $type) {
                    if (empty($templateArray[$type])) {
                        continue;
                    }

                    $html =
                        $basketView->getView(
                            $errorCode,
                            $templateArray[$type],
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

                    if (empty($errorCode)) {
                        $html =
                            $templateService->substituteMarkerArrayCached(
                                $html,
                                $billMarkerArray,
                                $subpartArray
                            );
                    } else {
                        $html = '';
                        $message = ErrorUtility::getMessage($languageObj, $errorCode);
                        $this->logger->error(
                            $message,
                            [$this->extensionKey]
                        );
                    }
                    if (!empty($html)) {
                        switch ($type) {
                            case BILL::HEADER:
                                    $pdf->setHeaderHtml($html);
                                break;
                            case BILL::FOOTER:
                                    $pdf->setFooterHtml($html);
                                break;
                        }
                    }
                }
            }

            // set default font subsetting mode
            $pdf->setFontSubsetting(true);

            // Set font
            // dejavusans is a UTF-8 Unicode font, if you only need to
            // print standard ASCII chars, you can use core fonts like
            // helvetica or times to reduce file size.
            $pdf->SetFont('dejavusans', '', 14, '', true);

            // Add a page
            // This method has several options, check the source code documentation for more information.
            $pdf->AddPage();

            $pdf->writeHTML($pdf_body);
            ob_clean();
            $pdf->Output($publicPath . $pdfFile, false);
            $result = $publicPath . $pdfFile;
        }

        return $result;
    }
}

