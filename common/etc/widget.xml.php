<?= '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<widgets xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Widget:etc/widget.xsd">
    <!--        TODO image for TP-->
    <widget id="<?= strtolower($argv[1]); ?>_summay_widget" class="Valued\Magento2\Block\Widget\Summary" placeholder_image="<?= $argv[1]; ?>::images/summary_widget.png">

		<label translate="true"><?= $argv[1]; ?> - Summary Widget</label>
		<description>Add <?= $argv[1]; ?> summary widget</description>
        <parameters>
			<parameter name="template" xsi:type="select" visible="true">
				<label translate="true">Template</label>
                <options>
                    <option name="small" value="<?= $argv[1]; ?>_Magento2::widget/small.phtml">
                        <label translate="true">Small Summary Widget</label>
                    </option>
                    <option name="big" value="<?= $argv[1]; ?>_Magento2::widget/big.phtml">
                        <label translate="true">Big Summary Widget</label>
                    </option>
                </options>
			</parameter>
			<parameter name="webwinkel_url" xsi:type="select" visible="true">
				<label translate="true"><?= $argv[1]; ?> Language Url</label>
				<options>
					<option name="default" value="default" selected="true">
						<label translate="true">Default</label>
					</option>
					<option name="nl" value="nl">
						<label translate="true">Dutch</label>
					</option>
					<option name="en" value="en">
						<label translate="true">English</label>
					</option>
					<option name="es" value="es">
						<label translate="true">Spanish</label>
					</option>
					<option name="de" value="de">
						<label translate="true">German</label>
					</option>
				</options>
			</parameter>
			<parameter name="rich_snippets" xsi:type="select" source_model="Magento\Config\Model\Config\Source\Yesno" visible="true" sort_order="2" >
				<label translate="true">Add Rich Snippets</label>
			</parameter>
            <parameter name="cache_lifetime" xsi:type="text" visible="true" sort_order="5">
                <label translate="true">Cache Lifetime (Seconds)</label>
                <description translate="true">86400 by default, if not set. To refresh instantly, clear the Blocks HTML Output cache.</description>
            </parameter>
        </parameters>
    </widget>
</widgets>
