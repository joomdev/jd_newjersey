<?php

N2Loader::import(array(
    'libraries.animations.storage'
));

class N2SystemAnimationModel extends N2SystemVisualModel
{

    public $type = 'animation';

    public function renderForm() {
        $form = new N2Form();
        $form->loadXMLFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'animation' . DIRECTORY_SEPARATOR . 'form.xml');
        $form->render('n2-animation-editor');
    }

    public function renderFormExtra() {
        $form = new N2Form();
        $form->loadXMLFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'animation' . DIRECTORY_SEPARATOR . 'extra.xml');
        $form->render('n2-animation-editor');
    }
}