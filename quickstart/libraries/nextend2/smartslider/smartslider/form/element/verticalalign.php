<?php
N2Loader::import('libraries.form.element.radiotab');

class N2ElementVerticalAlign extends N2ElementRadioTab {

    protected $class = 'n2-form-element-radio-tab n2-form-element-icon-radio';

    function generateOptions(&$xml) {
        $options = array();
        if (N2XmlHelper::getAttribute($xml, 'inherit')) {
            $options = $options + array(
                    'inherit' => 'n2-i n2-it n2-i-none'
                );
        }
        $options = $options + array(
                'flex-start'    => 'n2-i n2-it n2-i-vertical-align-top',
                'center'        => 'n2-i n2-it n2-i-vertical-align-center',
                'flex-end'      => 'n2-i n2-it n2-i-vertical-align-bottom',
                'space-between' => 'n2-i n2-it n2-i-vertical-align-space-between',
                'space-around'  => 'n2-i n2-it n2-i-vertical-align-space-around'
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