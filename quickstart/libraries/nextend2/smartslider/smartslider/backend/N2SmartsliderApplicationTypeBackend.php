<?php

class N2SmartsliderApplicationTypeBackend extends N2ApplicationType {

    public $type = "backend";

    protected function autoload() {

        N2Loader::import(array(
            'libraries.embedwidget.embedwidget',
            'libraries.plugin.plugin',
            'libraries.form.form',
            'libraries.image.color',
            'libraries.parse.parse'
        ));

        N2Loader::import(array(
            'libraries.settings.settings'
        ), 'smartslider');

        N2Loader::import('helpers.controller.N2SmartSliderController', 'smartslider.backend');
    }

    protected function onControllerReady() {
        $this->getLayout()
             ->addBreadcrumb(N2Html::tag('a', array(
                 'href'  => $this->router->createUrl("sliders/index"),
                 'class' => 'n2-h4'
             ), n2_('Dashboard')));

        N2JS::addFirstCode("window.N2SS3VERSION='" . N2SS3::$version . "';");
    }

}