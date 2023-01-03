<?php
namespace TrustProfile\Magento2\Setup;

use Valued\Magento2\Setup\ExtensionBase;

class Extension extends ExtensionBase {
    public function getSlug() {
        return 'trustprofile';
    }

    public function getModuleCode() {
        return 'TrustProfile_Magento2';
    }

    public function getName() {
        return 'TrustProfile';
    }

    public function getMainDomain() {
        return 'www.trustprofile.com';
    }

    public function getDashboardDomain() {
        return 'webwinkelkeur:0MxbXJ8C4WUEHcHZurJJ@staging.trustprofile.io';
    }
}