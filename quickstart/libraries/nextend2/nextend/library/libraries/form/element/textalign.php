<?php
N2Loader::import('libraries.form.element.radiotab');

class N2ElementTextAlign extends N2ElementRadioTab {

    protected $class = 'n2-form-element-radio-tab n2-form-element-textalign';

    function generateOptions(&$xml) {
        $options = array(
            'inherit' => 'n2-i n2-it n2-i-none',
            'left'    => 'n2-i n2-it n2-i-left',
            'center'  => 'n2-i n2-it n2-i-center',
            'right'   => 'n2-i n2-it n2-i-right',
            'justify' => 'n2-i n2-it n2-i-justify'
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