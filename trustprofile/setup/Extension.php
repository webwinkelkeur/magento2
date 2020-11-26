<?php


namespace TrustProfile\Magento2\trustprofile\setup;


use Valued\Magento2\common\setup\ExtensionBase;

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
        return 'www.trustprofile.io';
    }

    public function getDashboardDomain() {
        return 'dashboard.trustprofile.io';
    }

}