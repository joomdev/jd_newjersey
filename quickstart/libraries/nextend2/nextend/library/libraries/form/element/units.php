<?php
N2Loader::import('libraries.form.element.hidden');

class N2ElementUnits extends N2ElementHidden {

    public $_tooltip = true;

    function fetchElement() {

        $values = array();

        $html = "<div class='n2-form-element-units' style='" . N2XmlHelper::getAttribute($this->_xml, 'style') . "'>";

        $currentValue = $this->getValue();
        $currentLabel = '';

        $html .= N2Html::openTag('div', array(
            'class' => 'n2-element-units'
        ));
        foreach ($this->_xml->unit AS $unit) {
            $value    = (string)$unit->attributes()->value;
            $values[] = $value;

            $html .= N2Html::tag('div', array(
                'class' => 'n2-element-unit n2-h5 n2-uc '
            ), n2_((string)$unit));

            if ($currentValue == $value) {
                $currentLabel = n2_((string)$unit);
            }
        }

        $html .= "</div>";

        $html .= N2Html::tag('div', array(
            'class' => 'n2-element-current-unit n2-h5 n2-uc '
        ), $currentLabel);

        $html .= parent::fetchElement();

        $html .= "</div>";

        N2JS::addInline('new N2Classes.FormElementUnits("' . $this->_id . '", ' . json_encode($values) . ');');

        return $html;
    }
}
