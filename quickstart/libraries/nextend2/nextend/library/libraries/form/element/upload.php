<?php

N2Loader::import('libraries.form.element.text');

class N2ElementUpload extends N2ElementText
{

    public $fieldType = 'file';

    protected function getClass() {
        return 'n2-form-element-file ';
    }
}