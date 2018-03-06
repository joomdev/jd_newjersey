<?php

N2Loader::import('libraries.form.element.subform');

class N2ElementSubformImage extends N2ElementSubform
{

    protected $labels = array();
    var $_tooltip = false;

    function renderSelector() {
        $html = '<div style="display: none;">';
        $html .= parent::renderSelector();
        $html .= '</div>';
        foreach ($this->_list AS $k => $path) {
            $html .= $this->getOptionHtml($path, $k);
        }

        N2JS::addInline('
        new N2Classes.FormElementSubform(
               "' . $this->_id . '",
               "nextend-' . $this->_name . '-panel",
               "' . $this->_tab->_name . '",
               "' . $this->getValue() . '"
            );
        ');
        N2JS::addInline('
        new N2Classes.FormElementSubformImage(
              "' . $this->_id . '",
              "' . $this->_id . '_options"
            );
        ');

        $GLOBALS['nextend-' . $this->_name . '-panel'] = $this->renderForm();

        if (count($this->_list) <= 1) {
            $this->_xml->addAttribute('class', 'n2-hidden');
            $this->_tab->_hide = true;
        }

        return N2Html::tag('div', array(
            'class' => 'n2-subform-image ' . $this->getClass(),
            'id'    => $this->_id . '_options'
        ), $html);
    }

    function getOptionHtml($path, $k) {
        return N2Html::tag('div', array(
            'class' => 'n2-subform-image-option ' . $this->isActive($k)
        ), N2Html::tag('div', array(
                'class' => 'n2-subform-image-element',
                'style' => 'background-image: URL(' . $this->getImage($path, $k) . ');'
            )) . N2Html::tag('div', array(
                'class' => 'n2-subform-image-title n2-h4'
            ), $this->getLabel($k)));
    }

    function getImage($path, $key) {
        return N2Uri::pathToUri(N2Filesystem::translate($path . 'subformimage.png'));
    }

    function getLabel($key) {
        if (isset($this->labels[$key])) {
            return $this->labels[$key];
        }
        return ucfirst($key);
    }

    function isActive($value) {
        if (in_array($value, $this->_values)) {
            return 'n2-active';
        }
        return '';
    }

    function renderContainer() {
        return '';
    }

    function renderButton() {
        return '';
    }

    protected function getClass() {
        return '';
    }
}