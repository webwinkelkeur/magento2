<?php
namespace Valued\Magento2\Setup;

abstract class ExtensionBase {
    abstract public function getSlug();

    abstract public function getModuleCode();

    abstract public function getName();

    abstract public function getMainDomain();

    abstract public function getDashboardDomain();
}