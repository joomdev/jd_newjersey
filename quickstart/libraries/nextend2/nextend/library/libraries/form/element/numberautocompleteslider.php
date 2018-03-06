<?php
N2Loader::import('libraries.form.element.number');

class N2ElementNumberAutocompleteSlider extends N2ElementNumber {

    function fetchElement() {
        $html = parent::fetchElement();

        $units = array();
        if ($this->_xml->multiunit) {
            foreach ($this->_xml->multiunit AS $unit) {
                $units[N2XmlHelper::getAttribute($unit, 'value') . 'min'] = floatval(N2XmlHelper::getAttribute($unit, 'min'));
                $units[N2XmlHelper::getAttribute($unit, 'value') . 'max'] = floatval(N2XmlHelper::getAttribute($unit, 'slmax'));
            }
        }
        N2JS::addInline('new N2Classes.FormElementAutocompleteSlider("' . $this->_id . '", ' . json_encode(array(
                'min'   => floatval(N2XmlHelper::getAttribute($this->_xml, 'min')),
                'max'   => floatval(N2XmlHelper::getAttribute($this->_xml, 'slmax')),
                'step'  => floatval(N2XmlHelper::getAttribute($this->_xml, 'step')),
                'units' => $units
            )) . ');');
        return $html;
    }

    protected function getClass() {
        return 'n2-form-element-autocomplete ui-front ' . parent::getClass();
    }
}