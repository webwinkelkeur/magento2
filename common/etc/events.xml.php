<?php echo '<?xml version="1.0"?>';?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
	<event name="sales_order_save_after">
		<observer name="<?php echo strtolower($argv[1]) ;?>_review_invitation" instance="Valued\Magento2\Observer\OrderSave" />
	</event>
</config>
