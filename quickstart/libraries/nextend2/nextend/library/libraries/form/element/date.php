<?php

N2Loader::import('libraries.form.element.text');

class N2ElementDate extends N2ElementText {

    function fetchElement() {

        N2JS::addInline('$("#' . $this->_id . '").datetimepicker({lazyInit: true, format:"Y-m-d H:i:s", weeks: false, className: "n2", lang: "' . substr(N2Localization::getLocale(), 0, 2) . '"});');

        return parent::fetchElement();
    }

    protected function getStyle() {
        return N2XmlHelper::getAttribute($this->_xml, 'style') . '; text-align:center;';
    }
}
