<?php
N2Loader::import('libraries.form.element.text');

class N2ElementNumber extends N2ElementText {

    function fetchElement() {

        $min = N2XmlHelper::getAttribute($this->_xml, 'min');
        if ($min == '') {
            $min = '-Number.MAX_VALUE';
        }

        $max = N2XmlHelper::getAttribute($this->_xml, 'max');
        if ($max == '') {
            $max = 'Number.MAX_VALUE';
        }

        $units = false;
        if ($this->_xml->multiunit) {
            $units = array();
            foreach ($this->_xml->multiunit AS $unit) {
                $units[N2XmlHelper::getAttribute($unit, 'value') . 'min'] = floatval(N2XmlHelper::getAttribute($unit, 'min'));
                $units[N2XmlHelper::getAttribute($unit, 'value') . 'max'] = floatval(N2XmlHelper::getAttribute($unit, 'max'));
            }
        }

        N2JS::addInline('new N2Classes.FormElementNumber("' . $this->_id . '", ' . $min . ', ' . $max . ', ' . json_encode($units) . ');');

        $html = N2Html::openTag('div', array(
            'class' => 'n2-form-element-text ' . $this->getClass() . ($this->_xml->unit ? 'n2-text-has-unit ' : '') . 'n2-border-radius',
            'style' => ($this->fieldType == 'hidden' ? 'display: none;' : '')
        ));

        $subLabel = N2XmlHelper::getAttribute($this->_xml, 'sublabel');
        if ($subLabel) {
            $html .= N2Html::tag('div', array(
                'class' => 'n2-text-sub-label n2-h5 n2-uc'
            ), n2_($subLabel));
        }

        $html .= $this->pre();

        $html .= N2Html::tag('input', array(
            'type'         => $this->fieldType,
            'id'           => $this->_id,
            'name'         => $this->_inputname,
            'value'        => $this->_form->get($this->_name, $this->_default),
            'class'        => 'n2-h5',
            'style'        => $this->getStyle(),
            'autocomplete' => 'off'
        ), false);

        $html .= $this->post();

        if ($this->_xml->unit) {
            $html .= N2Html::tag('div', array(
                'class' => 'n2-text-unit n2-h5 n2-uc'
            ), n2_((string)$this->_xml->unit));
        }
        $html .= "</div>";
        return $html;
    }

    protected function getClass() {
        return 'n2-form-element-number ';
    }
}