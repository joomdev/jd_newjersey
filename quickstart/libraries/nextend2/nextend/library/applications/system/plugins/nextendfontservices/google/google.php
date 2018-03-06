<?php

class N2SystemPluginFontServiceGoogle extends N2PluginBase {

    /*
jQuery.getJSON('https://www.googleapis.com/webfonts/v1/webfonts?sort=alpha&key=AIzaSyBIzBtder0-ef5a6kX-Ri9IfzVwFu21PGw').done(function(data){
var f = [];
for(var i = 0; i < data.items.length; i++){
f.push(data.items[i].family);
}
console.log(JSON.stringify(f));
});
     */
    private static $fonts = array();

    var $_group = 'google';

    private static $styles = array();
    private static $subsets = array();

    public static function init() {
        $lines = file(dirname(__FILE__) . '/families.csv', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        for ($i = 0; $i < count($lines); $i++) {
            self::$fonts[strtolower($lines[$i])] = $lines[$i];
        }
        self::$fonts['droid sans']  = 'Noto Sans';
        self::$fonts['droid serif'] = 'Noto Serif';
    }


    function onFontServices(&$list) {
        $list[$this->_group] = array(
            'Google',
            $this->getPath(),
            1
        );
    }

    public static function getDefaults() {
        $defaults  = array();
        $fontsSets = explode(',', n2_x('latin', 'Default font sets'));
        for ($i = 0; $i < count($fontsSets); $i++) {
            $fontsSets[$i] = 'google-set-' . $fontsSets[$i];
        }
        $defaults += array_fill_keys($fontsSets, 1);

        return $defaults;
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'google' . DIRECTORY_SEPARATOR;
    }

    function onFontManagerLoad($force = false) {
        static $loaded;
        if (!$loaded || $force) {
            $loaded     = true;
            $settings   = N2Fonts::loadSettings();
            $parameters = $settings['plugins'];

            $parameters->fillDefault(self::getDefaults());

            if ($parameters->get('google-enabled', 1)) {
                N2GoogleFonts::$enabled = 1;

                for ($i = 100; $i < 1000; $i += 100) {
                    $this->addStyle($parameters, $i);
                    $this->addStyle($parameters, $i . 'italic');
                }
                if (empty(self::$styles)) {
                    self::$styles[] = '300';
                    self::$styles[] = '400';
                }

                $this->addSubset($parameters, 'latin');
                $this->addSubset($parameters, 'latin-ext');
                $this->addSubset($parameters, 'greek');
                $this->addSubset($parameters, 'greek-ext');
                $this->addSubset($parameters, 'cyrillic');
                $this->addSubset($parameters, 'devanagari');
                $this->addSubset($parameters, 'arabic');
                $this->addSubset($parameters, 'khmer');
                $this->addSubset($parameters, 'telugu');
                $this->addSubset($parameters, 'vietnamese');
                if (empty(self::$subsets)) {
                    self::$subsets[] = 'latin';
                }
                foreach (self::$subsets as $subset) {
                    N2GoogleFonts::addSubset($subset);
                }
                N2Pluggable::addAction('fontFamily', array(
                    $this,
                    'onFontFamily'
                ));
            }
        }
    }

    function onFontManagerLoadBackend() {
        N2JS::addInline('new NextendFontServiceGoogle("' . implode(',', self::$styles) . '","' . implode(',', self::$subsets) . '", ' . json_encode(self::$fonts) . ');');
    }

    function addStyle($parameters, $weight) {
        if ($parameters->get('google-style-' . $weight, 0)) {
            self::$styles[] = $weight;
        }
    }

    function addSubset($parameters, $subset) {
        if ($parameters->get('google-set-' . $subset, 0)) {
            self::$subsets[] = $subset;
        }
    }

    function onFontFamily($family) {
        $familyLower = strtolower($family);
        if (isset(self::$fonts[$familyLower])) {
            foreach (self::$styles AS $style) {
                N2GoogleFonts::addFont(self::$fonts[$familyLower], $style);
            }

            return self::$fonts[$familyLower];
        }

        return $family;
    }
}

N2SystemPluginFontServiceGoogle::init();

N2Plugin::addPlugin('fontservices', 'N2SystemPluginFontServiceGoogle');