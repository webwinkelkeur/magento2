<?php
namespace TrustProfile\Magento2;

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'TrustProfile_Magento2',
    isset($file) && realpath($file) == __FILE__ && getenv('VALUED_DEVELOPMENT') == 'yes'
        ? dirname($file) : __DIR__
);
