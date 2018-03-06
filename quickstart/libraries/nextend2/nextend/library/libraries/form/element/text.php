<?php

class N2ElementText extends N2Element
{

    protected $attributes = array();

    public $fieldType = 'text';

    function fetchElement() {

        N2JS::addInline('new N2Classes.FormElementText("' . $this->_id . '");');

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

        $html .= N2Html::tag('input', $this->attributes + array(
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
        return '';
    }

    protected function getStyle() {
        return N2XmlHelper::getAttribute($this->_xml, 'style');
    }

    protected function pre() {
        return '';
    }

    protected function post() {
        return '';
    }
}