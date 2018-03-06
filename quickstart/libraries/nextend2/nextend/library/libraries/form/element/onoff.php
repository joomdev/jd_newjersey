<?php
N2Loader::import('libraries.form.element.hidden');

class N2ElementOnoff extends N2ElementHidden
{

    public $_tooltip = true;

    function fetchElement() {
        $html = "<div class='n2-form-element-onoff " . $this->isOn() . "' style='" . N2XmlHelper::getAttribute($this->_xml, 'style') . "'>";
        $html .= N2Html::tag('div', array(
            'class' => 'n2-onoff-slider'
        ), N2Html::tag('div', array(
                'class' => 'n2-onoff-no'
            ), '<i class="n2-i n2-i-close"></i>') . N2Html::tag('div', array(
                'class' => 'n2-onoff-round'
            )) . N2Html::tag('div', array(
                'class' => 'n2-onoff-yes'
            ), '<i class="n2-i n2-i-tick"></i>'));
        $html .= parent::fetchElement();
        $html .= "</div>";

        N2JS::addInline('new N2Classes.FormElementOnoff("' . $this->_id . '");');
        return $html;
    }

    function isOn() {
        if ($this->getValue()) {
            return 'n2-onoff-on';
        }
        return '';
    }
}
