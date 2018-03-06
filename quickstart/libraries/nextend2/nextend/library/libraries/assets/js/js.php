<?php

class N2JS {

    public static function addFile($pathToFile, $group) {
        N2AssetsManager::$js->addFile($pathToFile, $group);
    }

    public static function addFiles($path, $files, $group) {
        N2AssetsManager::$js->addFiles($path, $files, $group);
    }

    public static function addStaticGroup($file, $group) {
        N2AssetsManager::$js->addStaticGroup($file, $group);
    }

    public static function addCode($code, $group) {
        N2AssetsManager::$js->addCode($code, $group);
    }

    public static function addUrl($url) {
        N2AssetsManager::$js->addUrl($url);
    }

    public static function addFirstCode($code, $unshift = false) {
        N2AssetsManager::$js->addFirstCode($code, $unshift);
    }

    public static function addInline($code, $global = false, $unshift = false) {
        N2AssetsManager::$js->addInline($code, $global, $unshift);
    }

    public static function addInlineFile($path, $global = false, $unshift = false) {
        static $loaded = array();
        if (!isset($loaded[$path])) {
            N2AssetsManager::$js->addInline(N2Filesystem::readFile($path), $global, $unshift);
            $loaded[$path] = 1;
        }
    }

    public static function jQuery($force = false, $overrideJQuerySetting = false) {
        if ($force) {
            if ($overrideJQuerySetting || N2Settings::get('jquery')) {
                self::addFiles(N2LIBRARYASSETS . '/js/core/jquery', array(
                    "jQuery.min.js"
                ), "n2");
            }
            self::addFiles(N2LIBRARYASSETS . '/js/core/jquery', array(
                "njQuery.js"
            ), "n2");
        } else if ($overrideJQuerySetting || N2Settings::get('jquery') || N2Platform::$isAdmin) {
            self::addFiles(N2LIBRARYASSETS . '/js/core/jquery', array(
                "jQuery.min.js",
                "njQuery.js"
            ), "n2");

        } else {
            if (N2Settings::get('async', '0')) {
                self::addInline(file_get_contents(N2LIBRARYASSETS . '/js/core/jquery/njQuery.js'), true);
            } else {
                self::addFiles(N2LIBRARYASSETS . '/js/core/jquery', array(
                    "njQuery.js"
                ), "n2");
            }
        }
    
    }

    public static function modernizr() {
        self::addFile(N2LIBRARYASSETS . '/js/core/modernizr/modernizr.js', "nextend-frontend");
    }

} 