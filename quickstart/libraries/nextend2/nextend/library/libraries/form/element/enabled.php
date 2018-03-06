<?php
N2Loader::import('libraries.form.element.onoff');

class N2ElementEnabled extends N2ElementOnOff
{

    function fetchElement() {
        N2JS::addInline('new N2Classes.FormElementEnabled("' . $this->_id . '", "' . N2XmlHelper::getAttribute($this->_xml, 'selector') . '");');
        return parent::fetchElement();
    }
}
