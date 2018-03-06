<?php
N2Loader::import('libraries.form.element.hidden');
N2Loader::import('libraries.stylemanager.stylemanager');

class N2ElementStyle extends N2ElementHidden {

    public $_tooltip = true;

    function fetchElement() {

        $preview = preg_replace_callback('/url\(\'(.*?)\'\)/', 'N2ElementStyle::fixPreviewImages', (string)$this->_xml);

        N2JS::addInline('new N2Classes.FormElementStyle("' . $this->_id . '", {
            previewmode: "' . N2XmlHelper::getAttribute($this->_xml, 'previewmode') . '",
            font: "' . N2XmlHelper::getAttribute($this->_xml, 'font') . '",
            font2: "' . N2XmlHelper::getAttribute($this->_xml, 'font2') . '",
            style2: "' . N2XmlHelper::getAttribute($this->_xml, 'style2') . '",
            preview: ' . json_encode($preview) . ',
            set: "' . N2XmlHelper::getAttribute($this->_xml, 'set') . '",
            label: "' . $this->_label . '"
        });');

        return N2Html::tag('div', array(
            'class' => 'n2-form-element-option-chooser n2-form-element-style n2-border-radius'
        ), parent::fetchElement() . N2Html::tag('input', array(
                'type'     => 'text',
                'class'    => 'n2-h5',
                'style'    => 'width: 130px;' . N2XmlHelper::getAttribute($this->_xml, 'css'),
                'data-disabled' => 'disabled'
            ), false) . N2Html::tag('a', array(
                'href'  => '#',
                'class' => 'n2-form-element-clear'
            ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-empty n2-i-grey-opacity'), '')) . N2Html::tag('a', array(
                'href'  => '#',
                'class' => 'n2-form-element-button n2-h5 n2-uc'
            ), n2_('Style')));
    }

    public static function fixPreviewImages($matches) {
        return "url(" . N2ImageHelper::fixed($matches[1]) . ")";
    }
}
