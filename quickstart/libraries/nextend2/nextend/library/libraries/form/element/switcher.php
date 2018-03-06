<?php
N2Loader::import('libraries.form.element.hidden');

class N2ElementSwitcher extends N2ElementHidden
{

    public $_tooltip = true;

    function fetchElement() {

        $values = array();

        $html = "<div class='n2-form-element-switcher' style='" . N2XmlHelper::getAttribute($this->_xml, 'style') . "'>";

        $i            = 0;
        $units        = count($this->_xml->unit) - 1;
        $currentValue = $this->getValue();
        foreach ($this->_xml->unit AS $unit) {
            $value           = (string)$unit->attributes()->value;
            $values[] = $value;

            $html .= N2Html::tag('div', array(
                'class' => 'n2-switcher-unit n2-h5 n2-uc ' . ($value == $currentValue ? 'n2-active ' : '') . ($i == 0 ? 'n2-first ' : '') . ($i == $units ? 'n2-last ' : '')
            ), n2_((string)$unit));
            $i++;
        }

        $html .= parent::fetchElement();

        $html .= "</div>";

        N2JS::addInline('new N2Classes.FormElementSwitcher("' . $this->_id . '", ' . json_encode($values) . ');');

        return $html;
    }
}
