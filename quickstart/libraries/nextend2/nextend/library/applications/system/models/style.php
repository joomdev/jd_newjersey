<?php

N2Loader::import(array(
    'libraries.stylemanager.storage'
));

class N2SystemStyleModel extends N2SystemVisualModel
{

    public $type = 'style';

    public function renderForm() {
        $form = new N2Form();
        $form->loadXMLFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR . 'form.xml');
        $form->render('n2-style-editor');
    }

    public function renderFormExtra() {
        $form = new N2Form();
        $form->loadXMLFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'style' . DIRECTORY_SEPARATOR . 'extra.xml');
        $form->render('n2-style-editor');
    }
}