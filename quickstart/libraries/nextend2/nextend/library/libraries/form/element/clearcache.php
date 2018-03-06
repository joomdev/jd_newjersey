<?php

class N2ElementClearCache extends N2ElementButton
{

    function fetchElement() {
        $this->_xml->addAttribute('url', $_SERVER['REQUEST_URI'] . '&nextendclearcache=1');
        //$html = '<a href="' . $_SERVER['REQUEST_URI'] . '&nextendclearcache=1" class="nextend-button-css nextend-font-export">' . NextendText::_($this->_label) . '</a>';
        return parent::fetchElement();
    }
}
