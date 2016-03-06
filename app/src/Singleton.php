<?php namespace MVCFAM\App;
/**
 * Define a reusable singleton trait.
 */
trait Singleton {
    
    private static $instance;

    public static function getInstance() {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public static function inst() {
        return static::getInstance();
    }
}