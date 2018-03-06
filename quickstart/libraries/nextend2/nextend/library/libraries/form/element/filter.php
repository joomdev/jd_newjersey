<?php
N2Loader::import('libraries.form.element.list');

class N2ElementFilter extends N2ElementList
{

    function generateOptions(&$xml) {
        $html = '';
        $html .= '<option value="0" ' . $this->isSelected(0) . '>' . n2_('All') . '</option>';
        $html .= '<option value="1" ' . $this->isSelected(1) . '>' . $this->_label . '</option>';
        $html .= '<option value="-1" ' . $this->isSelected(-1) . '>' . sprintf(n2_('Not %s'), $this->_label) . '</option>';
        return $html;
    }
}