<?php
N2Loader::import('libraries.form.element.hidden');

class N2ElementCheckbox extends N2ElementHidden
{

    public $_tooltip = true;

    protected $values = array();

    protected $value = null;

    function fetchElement() {

        $this->value = $this->getValue();

        $html = N2Html::tag('div', array(
            'class' => 'n2-form-element-checkbox',
            'style' => N2XmlHelper::getAttribute($this->_xml, 'style')
        ), $this->generateOptions($this->_xml) . parent::fetchElement());

        N2JS::addInline('new N2Classes.FormElementCheckbox("' . $this->_id . '", ' . json_encode($this->values) . ');');

        return $html;
    }

    function generateOptions(&$xml) {

        $html = '';

        foreach ($xml->option AS $option) {
            $v              = N2XmlHelper::getAttribute($option, 'value');
            $this->values[] = $v;

            $attributes = array(
                'class' => 'nextend-checkbox-option'
            );
            if ($this->isSelected($v)) {
                $attributes['selected'] = 'selected';
            }
            $html .= N2Html::tag('div', $attributes, (string)$option);
        }
        return $html;
    }

    function isSelected($value) {
        $values = explode('||', $this->value);
        if (in_array($value, $values)) {
            return true;
        }
        return false;
    }
}
