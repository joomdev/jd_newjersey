<?php

N2Loader::import('libraries.form.tab');

class N2TabRaw extends N2Tab
{

    function decorateGroupStart() {

    }

    function decorateGroupEnd() {

        echo "</div>";
    }

    function decorateElement(&$el, $out, $i) {

        echo $out[1];
    }

}