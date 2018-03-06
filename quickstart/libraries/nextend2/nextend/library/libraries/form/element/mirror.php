<?php
N2Loader::import('libraries.form.element.onoff');

class N2ElementMirror extends N2ElementOnOff
{

    function fetchElement() {
        N2JS::addInline('new N2Classes.FormElementMirror("' . $this->_id . '");');
        return parent::fetchElement();
    }
}
