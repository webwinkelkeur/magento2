<?= '<?xml version="1.0" ?>'; ?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    <preference for="Valued\Magento2\Setup\ExtensionBase"
                type="<?= $argv[1]; ?>\Magento2\Setup\Extension" />
</config>
