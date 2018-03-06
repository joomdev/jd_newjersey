<?php
N2Loader::import('libraries.form.element.radio');

class N2ElementRadioTab extends N2ElementRadio
{

    protected $class = 'n2-form-element-radio-tab';

    function generateOptions(&$xml) {

        $length = count($xml->option) - 1;

        $html = '';
        $i    = 0;
        foreach ($xml->option AS $option) {
            $value          = N2XmlHelper::getAttribute($option, 'value');
            $this->values[] = $value;
            $html .= N2Html::tag('div', array(
                'class' => 'n2-radio-option n2-h4' . ($this->isSelected($value) ? ' n2-active' : '') . ($i == 0 ? ' n2-first' : '') . ($i == $length ? ' n2-last' : '')
            ), n2_((string)$option));
            $i++;
        }
        return $html;
    }
}
