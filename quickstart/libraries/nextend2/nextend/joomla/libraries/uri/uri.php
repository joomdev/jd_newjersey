<?php

class N2Uri extends N2UriAbstract
{

    function __construct() {
        $this->_baseuri = rtrim(JURI::root(), '/');

        $this->_currentbase = JURI::base();

        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
            $this->_baseuri = str_replace('http://', 'https://', $this->_baseuri);
        }
        self::$scheme = parse_url($this->_baseuri, PHP_URL_SCHEME);
    }

    static function ajaxUri($query = '', $magento = 'nextendlibrary') {
        return JUri::current();
    }

}