<?php

class N2UriAbstract {

    var $_baseuri;

    var $_currentbase = '';

    public static $scheme = 'http';

    static function getInstance() {

        static $instance;
        if (!is_object($instance)) {
            $instance = new N2Uri();
        } // if

        return $instance;
    }

    static function setBaseUri($uri) {
        $i           = N2Uri::getInstance();
        $i->_baseuri = $uri;
    }

    static function getBaseUri() {
        $i = N2Uri::getInstance();

        return $i->_baseuri;
    }

    static function pathToUri($path, $protocol = true) {
        $i = N2Uri::getInstance();

        $from = array();
        $to   = array();

        $basePath = N2Filesystem::getBasePath();
        if ($basePath != '/' && $basePath != "\\") {
            $from[] = $basePath;
            $to[]   = '';
        }
        $from[] = DIRECTORY_SEPARATOR;
        $to[]   = '/';

        return ($protocol ? $i->_baseuri : preg_replace('/^http:/', '', $i->_baseuri)) . str_replace($from, $to, str_replace('/', DIRECTORY_SEPARATOR, $path));
    }

    static function ajaxUri($query = '', $magento = 'nextendlibrary') {
        $i = N2Uri::getInstance();

        return $i->_baseuri;
    }

    static function fixrelative($uri) {
        if (substr($uri, 0, 1) == '/' || strpos($uri, '://') !== false) return $uri;

        return self::getInstance()->_baseuri . $uri;
    }

    static function relativetoabsolute($uri) {
        if (substr($uri, 0, 1) == '/' || strpos($uri, '://') !== false) return $uri;

        return self::getInstance()->_currentbase . $uri;
    }
}

N2Loader::import("libraries.uri.uri", "platform");