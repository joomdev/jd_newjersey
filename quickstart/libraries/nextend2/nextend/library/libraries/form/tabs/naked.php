<?php

N2Loader::import('libraries.form.tab');

class N2TabNaked extends N2Tab
{

    function decorateGroupStart() {

    }

    function decorateGroupEnd() {

    }

    function decorateTitle() {

    }

    function decorateElement(&$el, $out, $i) {

        echo $out[1];
    }

}