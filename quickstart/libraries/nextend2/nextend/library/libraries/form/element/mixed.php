<?php
N2Loader::import('libraries.form.element.hidden');

class N2ElementMixed extends N2Element {

    var $_separator = '|*|';

    var $_translateable = true;

    function fetchElement() {

        $this->_translateable = N2XmlHelper::getAttribute($this->_xml, 'translateable');
        $this->_translateable = ($this->_translateable === '0' ? false : true);
        $default              = explode($this->_separator, $this->_default);
        $value                = explode($this->_separator, $this->getValue());
        $value                = $value + $default;

        $html            = "<div class='n2-form-element-mixed' style='" . N2XmlHelper::getAttribute($this->_xml, 'style') . "'>";
        $this->_elements = array();
        $i               = 0;
        foreach ($this->_xml->param AS $element) {
            $attrs = array();
            if (isset($element->attribute)) {
                foreach ($element->attribute AS $attr) {
                    $attrs[N2XmlHelper::getAttribute($attr, 'type')] = (string)$attr;
                }
            }
            $html .= N2Html::openTag('div', $attrs + array(
                    'class' => "n2-mixed-group " . N2XmlHelper::getAttribute($element, 'class'),
                    'style' => N2XmlHelper::getAttribute($element, 'mixedstyle')
                ));

            $class = N2Form::importElement(N2XmlHelper::getAttribute($element, 'type'));

            $element->addAttribute('name', $this->_name . '_' . $i);
            $element->addAttribute('hidename', 1);
            if (isset($value[$i])) $element->addAttribute('default', $value[$i]);
            $el = new $class($this->_form, $this, $element);

            $el->parent  = &$this;
            $elementHtml = $el->render($this->_name . $this->control_name, $this->_translateable);
            $html .= "<div class='n2-mixed-label'>";
            $html .= $elementHtml[0];
            $html .= "</div>";
            $html .= "<div class='n2-mixed-element'>";
            $html .= $elementHtml[1];
            $html .= "</div>";
            $this->_elements[$i] = $el->_id;
            $i++;
            $html .= "</div>";
        }
        $hidden     = new N2ElementHidden($this->_form, $this->_tab, $this->_xml);
        $hiddenhtml = $hidden->render($this->control_name, false);
        $html .= $hiddenhtml[1];
        $html .= "</div>";

        N2JS::addInline('new N2Classes.FormElementMixed("' . $this->_id . '", ' . json_encode($this->_elements) . ', "' . $this->_separator . '");');

        return $html;
    }
}
