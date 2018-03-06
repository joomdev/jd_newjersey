<?php
N2Loader::import('libraries.form.element.mixed');

class N2ElementMarginPadding extends N2ElementMixed {

    function fetchElement() {

        $this->_translateable = N2XmlHelper::getAttribute($this->_xml, 'translateable');
        $this->_translateable = ($this->_translateable === '0' ? false : true);
        $default              = explode($this->_separator, $this->_default);
        $value                = explode($this->_separator, $this->getValue());
        $value                = $value + $default;

        $html = "<div class='n2-form-element-connected-marginpadding' style='" . N2XmlHelper::getAttribute($this->_xml, 'style') . "'>";

        $html .= '<div class="n2-text-sub-label n2-h5 n2-uc"><i class="n2-i n2-it n2-i-layerunlink"></i></div>';
        $this->_elements = array();
        $i               = 0;
        foreach ($this->_xml->param AS $element) {
            $class = N2Form::importElement(N2XmlHelper::getAttribute($element, 'type'));

            $element->addAttribute('name', $this->_name . '_' . $i);
            $element->addAttribute('hidename', 1);
            if (isset($value[$i])) $element->addAttribute('default', $value[$i]);
            $el = new $class($this->_form, $this, $element);

            $el->parent  = &$this;
            $elementHtml = $el->render($this->_name . $this->control_name, $this->_translateable);
            $html .= $elementHtml[1];
            $this->_elements[$i] = $el->_id;
            $i++;
        }
        $hidden     = new N2ElementHidden($this->_form, $this->_tab, $this->_xml);
        $hiddenhtml = $hidden->render($this->control_name, false);
        $html .= $hiddenhtml[1];
        $html .= "</div>";

        N2JS::addInline('new N2Classes.FormElementMarginPadding("' . $this->_id . '", ' . json_encode($this->_elements) . ', "' . $this->_separator . '");');

        return $html;
    }
}