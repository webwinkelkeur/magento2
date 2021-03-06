<?php
namespace WebwinkelKeur\Magento2\Setup;

use Valued\Magento2\Setup\ExtensionBase;

class Extension extends ExtensionBase {
    public function getSlug() {
        return 'webwinkelkeur';
    }

    public function getModuleCode() {
        return 'WebwinkelKeur_Magento2';
    }

    public function getName() {
        return 'WebwinkelKeur';
    }

    public function getMainDomain() {
        return 'www.webwinkelkeur.nl';
    }

    public function getDashboardDomain() {
        return 'dashboard.webwinkelkeur.nl';
    }
}