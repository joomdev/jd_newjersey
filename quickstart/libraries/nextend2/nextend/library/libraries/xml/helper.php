<?php

class N2XmlHelper
{

    public static function getAttribute(&$xml, $attribute, $default = '') {
        if (isset($xml[$attribute])) {
            return (string)$xml[$attribute];
        }
        return $default;
    }
}