<?php
N2Loader::import('libraries.form.element.hidden');

class N2ElementRadio extends N2ElementHidden
{

    protected $class = 'n2-form-element-radio';

    protected $values = array();
    protected $value;

    public $_tooltip = true;

    function fetchElement() {

        $this->value = $this->getValue();

        $html = N2Html::tag('div', array(
            'class' => $this->class,
            'style' => N2XmlHelper::getAttribute($this->_xml, 'style')
        ), $this->generateOptions($this->_xml) . parent::fetchElement());

        N2JS::addInline('new N2Classes.FormElementRadio("' . $this->_id . '", ' . json_encode($this->values) . ');');

        return $html;
    }

    function generateOptions(&$xml) {

        $length = count($xml->option) - 1;

        $html = '';
        $i    = 0;
        foreach ($xml->option AS $option) {
            $v              = N2XmlHelper::getAttribute($option, 'value');
            $this->values[] = $v;
            $html .= N2Html::tag('div', array(
                    'class' => 'n2-radio-option n2-h4' . ($this->isSelected($v) ? ' n2-active' : '') . ($i == 0 ? ' n2-first' : '') . ($i == $length ? ' n2-last' : '')
                ), N2Html::tag('div', array(
                    'class' => 'n2-radio-option-marker'
                ), '<i class="n2-i n2-it n2-i-tick"></i>') . '<span>' . n2_((string)$option) . '</span>');
            $i++;
        }
        return $html;
    }

    function isSelected($value) {

        if ($value == $this->value) {
            return true;
        }
        return false;
    }
}
