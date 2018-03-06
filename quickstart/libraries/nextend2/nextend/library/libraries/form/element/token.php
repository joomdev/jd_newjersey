<?php

class N2ElementToken extends N2Element
{

    public $_mode = 'hidden';

    public $_tooltip = false;

    function fetchTooltip() {
        return $this->fetchNoTooltip();
    }

    function fetchElement() {
        $this->_xml->addAttribute('class', 'n2-hidden');
        return N2Form::tokenize();
    }
}
