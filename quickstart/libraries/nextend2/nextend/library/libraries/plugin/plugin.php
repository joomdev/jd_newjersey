<?php

class N2Pluggable
{

    static $classes = array();

    static function addAction($eventName, $callable) {
        if (!isset(self::$classes[$eventName])) self::$classes[$eventName] = array();
        self::$classes[$eventName][] = $callable;
    }

    static function applyFilters($eventName, $value, $args = array()) {
        if (self::hasAction($eventName)) {
            foreach (self::$classes[$eventName] AS $callable) {
                if (is_callable($callable)) {
                    $value = call_user_func_array($callable, array_merge(array($value), $args));
                }
            }
        }
        return $value;
    }

    static function doAction($eventName, $args = array()) {
        if (self::hasAction($eventName)) {
            foreach (self::$classes[$eventName] AS $callable) {
                if (is_callable($callable)) {
                    call_user_func_array($callable, $args);
                }
            }
        }
    }

    static function hasAction($eventName) {
        if (isset(self::$classes[$eventName])) {
            return true;
        }
        return false;
    }
}

class N2Plugin
{

    static $classes = array();

    static function addPlugin($group, $class, $custom = false) {
        if (!isset(self::$classes[$group])) self::$classes[$group] = array();
        if (!is_object($class)) $class = new $class();
        self::$classes[$group][] = $class;
    }

    static function callPlugin($group, $method, $args = array()) {
        if (isset(self::$classes[$group])) {
            foreach (self::$classes[$group] AS $class) {
                if (is_callable(array(
                    $class,
                    $method
                ))) {
                    call_user_func_array(array(
                        $class,
                        $method
                    ), $args);
                }
            }
        }
    }
}

class N2PluginBase
{

}