<?php
namespace TrustProfile\Magento2;

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'TrustProfile_Magento2',
    isset($file) && realpath($file) == __FILE__ && getenv('VALUED_DEVELOPMENT') == 'yes'
        ? dirname($file) : __DIR__
);

spl_autoload_register(function ($cls) {
    $prefix = __NAMESPACE__ . '\\Controller\\Adminhtml\\';
    if (strpos($cls, $prefix) !== 0) {
        return;
    }
    $base_class = 'Valued\\Magento2\\Controller\\Adminhtml\\Actions\\Import';
    if (!class_exists($base_class)) {
        return;
    }
    class_alias($base_class, $cls);
});
