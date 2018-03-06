<?php
N2Loader::import('libraries.form.element.number');
N2Loader::import('libraries.form.element.autocomplete');

class N2ElementAutocompleteSlider extends N2ElementAutocomplete {

    function fetchElement() {
        $html = parent::fetchElement();
        N2JS::addInline('new N2Classes.FormElementAutocompleteSlider("' . $this->_id . '", ' . json_encode(array(
                'min'  => floatval(N2XmlHelper::getAttribute($this->_xml, 'min')),
                'max'  => floatval(N2XmlHelper::getAttribute($this->_xml, 'max')),
                'step' => floatval(N2XmlHelper::getAttribute($this->_xml, 'step'))
            )) . ');');
        return $html;
    }

    protected function getClass() {
        return 'n2-form-element-autocomplete ui-front ' . parent::getClass();
    }
}