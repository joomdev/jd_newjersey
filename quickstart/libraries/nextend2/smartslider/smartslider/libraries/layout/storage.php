<?php

class N2SmartSliderLayoutStorage
{

    private static $sets = array();

    private static $visual = array();

    private static $visualBySet = array();

    private static $visualById = array();

    public static function init() {
        N2Pluggable::addAction('smartsliderlayoutset', 'N2SmartSliderLayoutStorage::visualSet');
        N2Pluggable::addAction('smartsliderlayout', 'N2SmartSliderLayoutStorage::visuals');
        N2Pluggable::addAction('layout', 'N2SmartSliderLayoutStorage::visual');
    }

    private static function load() {
        static $loaded;
        if (!$loaded) {
            N2Pluggable::doAction('layoutStorage', array(
                &self::$sets,
                &self::$visual
            ));

            for ($i = 0; $i < count(self::$visual); $i++) {
                if (!is_array(self::$visualBySet[self::$visual[$i]['referencekey']])) {
                    self::$visualBySet[self::$visual[$i]['referencekey']] = array();
                }
                self::$visualBySet[self::$visual[$i]['referencekey']][] = &self::$visual[$i];
                self::$visualById[self::$visual[$i]['id']]              = &self::$visual[$i];
            }
            $loaded = true;
        }
    }

    public static function visualSet($referenceKey, &$sets) {
        self::load();

        for ($i = count(self::$sets) - 1; $i >= 0; $i--) {
            self::$sets[$i]['system']   = 1;
            self::$sets[$i]['editable'] = 0;
            array_unshift($sets, self::$sets[$i]);
        }

    }

    public static function visuals($referenceKey, &$visuals) {
        self::load();
        if (isset(self::$visualBySet[$referenceKey])) {
            $_visual = &self::$visualBySet[$referenceKey];
            for ($i = count($_visual) - 1; $i >= 0; $i--) {
                $_visual[$i]['system']   = 1;
                $_visual[$i]['editable'] = 0;
                array_unshift($visuals, $_visual[$i]);
            }

        }
    }

    public static function visual($id, &$visual) {
        self::load();
        if (isset(self::$visualById[$id])) {
            self::$visualById[$id]['system']   = 1;
            self::$visualById[$id]['editable'] = 0;
            $visual                            = self::$visualById[$id];
        }
    }
}

N2SmartSliderLayoutStorage::init();