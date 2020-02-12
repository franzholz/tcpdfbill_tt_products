# TYPO3 extension tcpdfbill_tt_products

The TYPO3 extension tcpdfbill_tt_products has the purpose to enable the generation of a bill in a PDF format. A HTML bill is converted into a PDF bill using the TCPDF library. The TCPDF library is not included in this extension. tt_products makes usage of this extension.

Put this into the Setup:

## Generate bill:

```
plugin.tt_products {
  bill.generation = auto
  bill.conf {
     path = fileadmin/data/bill
  }
  orderEmail {
    10002.attachment = bill
  }
}
```

Use the setup bill.conf to overwrite the charset and the standard configuration attributes of TCPDF.
This extension contains a HTML template file which you can move into the fileadmin folder if you want to adapt it to your needs.


```
plugin.tt_products {
  bill.conf {
     templateFile = fileadmin/pdf_template.html
     font {
        style = normal
     }
  }
}
```


## TCPDF Library:

Use the Extension Manager to set the relative library path to TCPDF, where the TYPO3 home directory is the starting point.

```
libraryPath = tcpdf
```

If you have installed the extension t3_tcpdf, then you must use this configuration:

```
libraryPath = typo3conf/ext/t3_tcpdf/Resources/Private/PHP/tcpdf
```

For extension tcpdf you must use this path:

```
libraryPath = typo3conf/ext/tcpdf/Resources/Private/Library/tcpdf
```


Only the older library is supported, where the file /tcpdf/tcpdf.php must exist.
For PHP 7 you must get a fork of it at:

https://github.com/semaex/TCPDF



