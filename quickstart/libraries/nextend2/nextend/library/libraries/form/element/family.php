<?php
N2Loader::import('libraries.form.element.text');

class N2ElementFamily extends N2ElementText {

    function fetchElement() {
        $html         = parent::fetchElement();
        $fontSettings = N2Fonts::loadSettings();
        $families     = explode("\n", $fontSettings['preset-families']);

        usort($families, 'N2ElementFamily::sort');
        N2JS::addInline('new N2Classes.FormElementAutocompleteSimple("' . $this->_id . '", ' . json_encode($families) . ');');
        return $html;
    }

    protected function getClass() {
        return 'n2-form-element-autocomplete ui-front ' . parent::getClass();
    }

    public static function sort($a, $b) {
        $a = preg_replace('|[^a-zA-Z0-9 ]|', '', $a);
        $b = preg_replace('|[^a-zA-Z0-9 ]|', '', $b);

        return strnatcmp($a, $b);
    }
}