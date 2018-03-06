<?php
N2Loader::import('libraries.form.element.hidden');

class N2ElementList extends N2ElementHidden
{

    var $_tooltip = true;

    var $_translateable = false;
    public $value;

    function fetchElement() {

        $this->_values = explode('||', $this->getValue());
        if (!is_array($this->_values)) {
            $this->_values = array();
        }
        $this->_multiple = intval(N2XmlHelper::getAttribute($this->_xml, 'multiple'));

        $this->_translateable = intval(N2XmlHelper::getAttribute($this->_xml, 'translateable'));

        $size = N2XmlHelper::getAttribute($this->_xml, 'size');
        if ($size != '') $size = " size='" . $size . "'";

        $html = N2Html::openTag("div", array(
            "class" => "n2-form-element-list",
            "style" => N2XmlHelper::getAttribute($this->_xml, 'style')
        ));
        $html .= "<select id='" . $this->_id . "_select' name='select" . $this->_inputname . "' " . $size . $this->isMultiple() . "  autocomplete='off'>";
        $html .= $this->generateOptions($this->_xml);
        if ($this->_xml->optgroup) {
            $html .= $this->generateOptgroup($this->_xml);
        }
        $html .= "</select>";
        $html .= N2Html::closeTag("div");

        $html .= parent::fetchElement();

        N2JS::addInline('new N2Classes.FormElementList("' . $this->_id . '", ' . $this->_multiple . ', "' . $this->getValue() . '");');

        return $html;
    }

    function generateOptgroup(&$xml) {
        $html = '';
        foreach ($xml->optgroup AS $optgroup) {
            $label = N2XmlHelper::getAttribute($optgroup, 'label');
            $html .= "<optgroup label='" . n2_($label) . "'>";
            $html .= $this->generateOptions($optgroup);
            $html .= "</optgroup>";
        }
        return $html;
    }

    function generateOptions(&$xml) {
        $html = '';
        foreach ($xml->option AS $option) {
            $v = N2XmlHelper::getAttribute($option, 'value');
            $html .= '<option value="' . $v . '" ' . $this->isSelected($v) . '>' . ($this->_translateable ? n2_((string)$option) : ((string)$option)) . '</option>';
        }
        return $html;
    }

    function isSelected($value) {
        if (in_array($value, $this->_values)) {
            return ' selected="selected"';
        }
        return '';
    }

    function isMultiple() {
        if ($this->_multiple) return ' multiple="multiple" class="nextend-element-hastip" title="' . n2_('Hold down the ctrl (Windows) or command (MAC) button to select multiple options.') . '" ';
        return '';
    }
}