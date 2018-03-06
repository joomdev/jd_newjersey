<?php
N2Loader::import('libraries.form.element.radiotab');

class N2ElementHAlign extends N2ElementRadioTab {

    protected $class = 'n2-form-element-radio-tab n2-form-element-icon-radio';

    function generateOptions(&$xml) {
        $options = array();
        if (N2XmlHelper::getAttribute($xml, 'inherit')) {
            $options = $options + array(
                    'inherit' => 'n2-i n2-it n2-i-none'
                );
        }
        $options = $options + array(
                'left'   => 'n2-i n2-it n2-i-horizontal-left',
                'center' => 'n2-i n2-it n2-i-horizontal-center',
                'right'  => 'n2-i n2-it n2-i-horizontal-right'
            );
        $length  = count($options) - 1;

        $this->values = array();
        $html         = '';
        $i            = 0;
        foreach ($options AS $value => $class) {
            $this->values[] = $value;

            $html .= N2Html::tag('div', array(
                'class' => 'n2-radio-option' . ($this->isSelected($value) ? ' n2-active' : '') . ($i == 0 ? ' n2-first' : '') . ($i == $length ? ' n2-last' : '')
            ), N2Html::tag('i', array('class' => $class)));
            $i++;
        }

        return $html;
    }
}