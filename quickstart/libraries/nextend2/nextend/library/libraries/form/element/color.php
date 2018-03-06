<?php
N2Loader::import('libraries.form.element.text');

class N2ElementColor extends N2ElementText
{

    protected $alpha = 0;

    function fetchElement() {

        if (N2XmlHelper::getAttribute($this->_xml, 'alpha') == 1) {
            $this->alpha = 1;
        }

        $html = parent::fetchElement();
        N2JS::addInline('new N2Classes.FormElementColor("' . $this->_id . '", ' . $this->alpha . ');');
        return $html;
    }

    protected function getClass() {
        return 'n2-form-element-color ' . ($this->alpha ? 'n2-form-element-color-alpha ' : '');
    }

    protected function pre() {
        return '<div class="n2-sp-replacer"><div class="n2-sp-preview"><div class="n2-sp-preview-inner" style="background-color: rgb(62, 62, 62);"></div></div><div class="n2-sp-dd">&#9650;</div></div>';
    }

    protected function post() {
        return '';
    }
}
