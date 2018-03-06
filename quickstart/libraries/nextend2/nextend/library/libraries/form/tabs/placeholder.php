<?php
N2Loader::import('libraries.form.tab');

class N2TabPlaceholder extends N2Tab
{

    function decorateTitle() {
        $id = N2XmlHelper::getAttribute($this->_xml, 'id');
        echo "<div id='" . $id . "' class='nextend-tab " . N2XmlHelper::getAttribute($this->_xml, 'class') . "'>";
        if (isset($GLOBALS[$id])) {
            echo $GLOBALS[$id];
        }
    }

    function decorateGroupStart() {

    }

    function decorateGroupEnd() {
        echo "</div>";
    }
}