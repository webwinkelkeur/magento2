<?php
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'WebwinkelKeur_Magento2',
    isset($file) && realpath($file) == __FILE__ && getenv('VALUED_DEVELOPMENT') == 'yes'
        ? dirname($file) : __DIR__
);
