<?php echo '<?xml version="1.0"?>';?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
   <head>
        <css src="<?php echo $argv[1] ;?>::css/widget.css" />
   </head>
   <referenceContainer name="before.body.end">
        <block class="Valued\Magento2\Block\Sidebar" template="<?php echo $argv[1] ;?>_Magento2::sidebar.phtml" name="module_js"/>
    </referenceContainer>
</page>

