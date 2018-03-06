<?php

class N2SmartSliderBackupSimple {

    /**
     * @param N2SmartSliderExport      $export
     * @param                          $slider
     */
    public static function export($export, $slider) {
        $export->addImage($slider['params']->get('background', ''));
        $export->addImage($slider['params']->get('backgroundVideoMp4', ''));
    }

    /**
     * @param N2SmartSliderImport      $import
     * @param                          $slider
     */
    public static function import($import, $slider) {

        $slider['params']->set('background', $import->fixImage($slider['params']->get('background', '')));
        $slider['params']->set('backgroundVideoMp4', $import->fixImage($slider['params']->get('backgroundVideoMp4', '')));
    }
}