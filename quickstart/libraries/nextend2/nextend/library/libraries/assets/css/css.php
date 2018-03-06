<?php

class N2CSS
{

    public static function addFile($pathToFile, $group) {
        N2AssetsManager::$css->addFile($pathToFile, $group);
    }

    public static function addFiles($path, $files, $group) {
        N2AssetsManager::$css->addFiles($path, $files, $group);
    }

    public static function addStaticGroup($file, $group) {
        N2AssetsManager::$css->addStaticGroup($file, $group);
    }

    public static function addCode($code, $group) {
        N2AssetsManager::$css->addCode($code, $group);
    }

    public static function addUrl($url) {
        N2AssetsManager::$css->addUrl($url);
    }

    public static function addInline($code) {
        N2AssetsManager::$css->addInline($code);
    }

}