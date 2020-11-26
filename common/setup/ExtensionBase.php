<?php


namespace Valued\Magento2\common\setup;


abstract class ExtensionBase {
    protected static $instances = [];

    abstract public function getSlug();

    abstract public function getModuleCode();

    abstract public function getName();

    abstract public function getMainDomain();

    abstract public function getDashboardDomain();

    public static function getInstance() {
        if (!isset(self::$instances[static::class])) {
            self::$instances[static::class] = new static();
        }
        return self::$instances[static::class];
    }

    public function init() {
    }


}