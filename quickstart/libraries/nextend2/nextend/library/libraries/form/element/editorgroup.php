<?php

class N2ElementEditorGroup extends N2ElementGroup {

    var $_translateable = true;

    function fetchTooltip() {
        if ($this->_label == '-') {
            $this->_label = '';
        } else {
            $this->_label = n2_($this->_label);
        }
        $html = N2Html::tag('div', array(
            'class' => 'n2-editor-header n2-h2 n2-uc'
        ), '<span>' . $this->_label . '</span>');
        return $html;
    }
}