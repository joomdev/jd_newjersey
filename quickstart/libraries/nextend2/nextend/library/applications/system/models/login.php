<?php


class N2SystemLoginModel extends N2Model
{

    public static function renderForm() {

        $configurationXmlFile = dirname(__FILE__) . '/forms/login.xml';

        N2Loader::import('libraries.form.form');
        $form = new N2Form(N2Base::getApplication('system')->getApplicationType('backend'));


        $form->loadXMLFile($configurationXmlFile);

        return $form->render('login');
    }
}