<?php echo '<?xml version="1.0" encoding="UTF-8"?>';?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:App/etc/routes.xsd">
    <router id="admin">
        <route id="<?php echo strtolower($argv[1]) ;?>" frontName="<?php echo strtolower($argv[1]) ;?>">
            <module name="<?php echo $argv[1] ;?>_Magento2" />
        </route>
    </router>
</config>