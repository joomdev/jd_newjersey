<?php
N2Loader::import('libraries.form.element.hidden');
N2Loader::import('libraries.fonts.fontmanager');

class N2ElementFont extends N2ElementHidden {

    public $_tooltip = true;

    function fetchElement() {

        N2JS::addInline('new N2Classes.FormElementFont("' . $this->_id . '", {
            previewmode: "' . N2XmlHelper::getAttribute($this->_xml, 'previewmode') . '",
            style: "' . N2XmlHelper::getAttribute($this->_xml, 'style') . '",
            style2: "' . N2XmlHelper::getAttribute($this->_xml, 'style2') . '",
            preview: ' . json_encode((string)$this->_xml) . ',
            set: "' . N2XmlHelper::getAttribute($this->_xml, 'set') . '",
            label: "' . $this->_label . '"
        });');

        return N2Html::tag('div', array(
            'class' => 'n2-form-element-option-chooser n2-form-element-font n2-border-radius'
        ), parent::fetchElement() . N2Html::tag('input', array(
                'type'          => 'text',
                'class'         => 'n2-h5',
                'style'         => 'width: 130px;' . N2XmlHelper::getAttribute($this->_xml, 'css'),
                'data-disabled' => 'disabled'
            ), false) . N2Html::tag('a', array(
                'href'  => '#',
                'class' => 'n2-form-element-clear'
            ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-empty n2-i-grey-opacity'), '')) . N2Html::tag('a', array(
                'href'  => '#',
                'class' => 'n2-form-element-button n2-h5 n2-uc'
            ), n2_('Font')));
    }
}
