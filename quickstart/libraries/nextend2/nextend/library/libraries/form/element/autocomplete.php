<?php

class N2ElementAutocomplete extends N2ElementText
{

    protected $attributes = array();

    public $fieldType = 'text';

    function fetchElement() {
        $html = parent::fetchElement();

        $options = array();
        if (count($this->_xml->option)) {
            foreach ($this->_xml->option AS $option) {
                $options[] = (string)$option;
            }
        }
        N2JS::addInline('new N2Classes.FormElementAutocomplete("' . $this->_id . '", ' . json_encode($options) . ');');

        return $html;
    }

    protected function getClass() {
        return 'n2-form-element-autocomplete ui-front ';
    }

    protected function getStyle() {
        return N2XmlHelper::getAttribute($this->_xml, 'style');
    }

    protected function pre() {
        return '';
    }

    protected function post() {
        return N2Html::tag('a', array(
            'href'  => '#',
            'class' => 'n2-form-element-clear'
        ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-empty n2-i-grey-opacity'), ''));
    }
}