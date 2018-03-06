<?php

class N2Fonts {

    private static $config;

    public static function loadSettings() {
        static $inited;
        if (!$inited) {
            $inited       = true;
            self::$config = array(
                'default-family'  => n2_x('Roboto,Arial', 'Default font'),
                'preset-families' => n2_x(implode("\n", array(
                    "Abel",
                    "Arial",
                    "Arimo",
                    "Average",
                    "Bevan",
                    "Bitter",
                    "'Bree Serif'",
                    "Cabin",
                    "Calligraffitti",
                    "Chewy",
                    "Comfortaa",
                    "'Covered By Your Grace'",
                    "'Crafty Girls'",
                    "'Dancing Script'",
                    "'Noto Sans'",
                    "'Noto Serif'",
                    "'Francois One'",
                    "'Fredoka One'",
                    "'Gloria Hallelujah'",
                    "'Happy Monkey'",
                    "'Josefin Slab'",
                    "Lato",
                    "Lobster",
                    "'Luckiest Guy'",
                    "Montserrat",
                    "'Nova Square'",
                    "Nunito",
                    "'Open Sans'",
                    "Oswald",
                    "Oxygen",
                    "Pacifico",
                    "'Permanent Marker'",
                    "'Playfair Display'",
                    "'PT Sans'",
                    "'Poiret One'",
                    "Raleway",
                    "Roboto",
                    "'Rock Salt'",
                    "Quicksand",
                    "Satisfy",
                    "'Squada One'",
                    "'The Girl Next Door'",
                    "'Titillium Web'",
                    "'Varela Round'",
                    "Vollkorn",
                    "'Walter Turncoat'"
                )), 'Default font family list'),
                'plugins'         => array()
            );
            foreach (N2StorageSectionAdmin::getAll('system', 'fonts') AS $data) {
                self::$config[$data['referencekey']] = $data['value'];
            }
            self::$config['plugins'] = new N2Data(self::$config['plugins'], true);
        }
        return self::$config;
    }

    public static function storeSettings($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (isset(self::$config[$key])) {
                    self::$config[$key] = $value;
                    N2StorageSectionAdmin::set('system', 'fonts', $key, $value, 1, 1);
                    unset($data[$key]);
                }
            }
            if (count($data)) {
                self::$config['plugins'] = new N2Data($data);
                N2StorageSectionAdmin::set('system', 'fonts', 'plugins', self::$config['plugins']->toJSON(), 1, 1);

            }
            return true;
        }
        return false;
    }

}

if (class_exists('N2FontRenderer', false)) {
    $fontSettings                = N2Fonts::loadSettings();
    N2FontRenderer::$defaultFont = $fontSettings['default-family'];
}