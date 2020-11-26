<?php echo '<?xml version="1.0"?>';?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Cron:etc/crontab.xsd">
	<group id="default">
		<job name="<?php echo strtolower($argv[1]) ;?>_import_reviews" instance="Valued\Magento2\Cron\ImportReviews" method="execute">
			<schedule>0 * * * *</schedule>
		</job>
	</group>
</config>