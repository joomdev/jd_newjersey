<?php
N2Loader::import('libraries.form.element.checkbox');

class N2ElementDecoration extends N2ElementCheckbox
{

    function fetchElement() {

        return N2Html::tag('div', array(
            'class' => 'n2-form-element-decoration',
            'style' => N2XmlHelper::getAttribute($this->_xml, 'style')
        ), parent::fetchElement());
    }

    function generateOptions(&$xml) {
        $options = array(
            'italic'    => 'n2-i n2-it n2-i-italic',
            'underline' => 'n2-i n2-it n2-i-underline'
        );

        $length = count($options) - 1;

        $html = '';
        $i    = 0;
        foreach ($options AS $value => $class) {
            $this->values[] = $value;

            $html .= N2Html::tag('div', array(
                'class' => 'n2-checkbox-option n2-decoration-' . $value . ($this->isSelected($value) ? ' n2-active' : '') . ($i == 0 ? ' n2-first' : '') . ($i == $length ? ' n2-last' : '')
            ), N2Html::tag('i', array('class' => $class)));
            $i++;
        }
        return $html;
    }
}