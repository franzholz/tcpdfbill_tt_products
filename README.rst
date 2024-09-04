TYPO3 extension tcpdfbill_tt_products
=====================================

The TYPO3 extension tcpdfbill_tt_products has the purpose to enable the
generation of a bill in a PDF format. A HTML bill is converted into a
PDF bill using the TCPDF library. The TCPDF library is not part of
this extension. However tt_products requires it. Therefore you must find your
prefered way of installing it.

3 possibilities:

#.  Install the extension t3_tcpdf or tcpdf.
#.  Use composer to install "tecnickcom/tcpdf".
#.  Copy an extracted folder of TCPDF anywhere on the filesystem below your Apache root.

Put this into the Setup:

Generate bill:
--------------

::

   plugin.tt_products {
     bill {
        generation = auto
        conf {
           path = fileadmin/data/bill
        }
     }
     orderEmail {
       10002.attachment = bill
     }
   }

Use the setup "bill.conf" to overwrite the charset and the standard
configuration attributes of TCPDF. Any other tt_products setup below "bill" is not available with this extension.
This extension contains a HTML marker template file which you can move below the
fileadmin folder in order to adapt it to your needs. Then you can add your company name,
account number and modify the design of the marker template file.
The PDF file will be generated and stored in the folder 'fileadmin/data' by default.
Use the 'outputFolder' setup to change it.

::

   plugin.tt_products {
     bill.conf {
        templateFile {
             body = fileadmin/body_template.html
             header = fileadmin/header_template.html
             footer = fileadmin/footer_template.html
        }
        font {
           style = normal
        }
     }
     outputFolder = fileadmin/data
   }


TCPDF Library:
--------------

Use the TYPO3 backend settings "Extension Configuration"
to set the relative library path to TCPDF
where the TYPO3 home directory is the starting point.

::

   libraryPath = tcpdf

If you have installed the extension t3_tcpdf, then you must use this
configuration:

::

   libraryPath = typo3conf/ext/t3_tcpdf/Resources/Private/PHP/tcpdf

For extension tcpdf you must use this path:

::

   libraryPath = typo3conf/ext/tcpdf/Resources/Private/Library/tcpdf

Only the older library is supported, where the file /tcpdf/tcpdf.php
must exist. For PHP 7 you must get a fork of it at:

https://github.com/semaex/TCPDF
