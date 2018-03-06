<?php

class N2Ini
{

    static function parse($file) {
        $strings = array();
        $handle  = fopen($file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                $string = explode('=', $line, 2);
                if (count($string) == 2) {
                    $tmp       = trim($string[1]);
                    $string[1] = trim($tmp, '"');
                    if ($string[1] != $tmp) {
                        $string[1] = stripslashes($string[1]);
                    }
                    $strings[trim($string[0])] = $string[1];
                }
            }
        }
        fclose($handle);
        return $strings;
    }
}