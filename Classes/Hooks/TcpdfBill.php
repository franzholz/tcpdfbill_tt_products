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


class TcpdfBill extends \TCPDF
{
    // header
    private $headerHtml = '';

    // footer
    private $footerHtml = '';


    /**
     * @param string $headerHtml
     */
    public function setHeaderHtml($headerHtml)
    {
        $this->headerHtml = $headerHtml;
    }

    /**
     * @param string $footerHtml
     */
    public function setFooterHtml($footerHtml)
    {
        $this->footerHtml = $footerHtml;
    }

    /** 
    * Overwrites the default header 
    * set the text in the view using 
    *    $fpdf->xheadertext = 'YOUR ORGANIZATION'; 
    * set the fill color in the view using 
    *    $fpdf->xheadercolor = array(0,0,100); (r, g, b) 
    * set the font in the view using 
    *    $fpdf->setHeaderFont(array('YourFont','',fontsize)); 
    */ 
    public function Header() 
    {
        $w = 0;
        $h = 0;
        $x = '';
        $y = '';
        $border = 0;
        $ln = 0;
        $fill = false;
        $reseth = true;
        $align = '';
        $autopadding = true;

        $this->writeHTMLCell($w, $h, $x, $y, $this->headerHtml, $border, $ln, $fill, $reseth, $align, $autopadding);
    }


    public function Footer()
    {
        $w = 0;
        $h = 0;
        $x = '';
        $y = '';
        $border = 0;
        $ln = 0;
        $fill = false;
        $reseth = true;
        $align = '';
        $autopadding = true;

        $this->writeHTMLCell($w, $h, $x, $y, $this->footerHtml, $border, $ln, $fill, $reseth, $align, $autopadding);
    }
}



