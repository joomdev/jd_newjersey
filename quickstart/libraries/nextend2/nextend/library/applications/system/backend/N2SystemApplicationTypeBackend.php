<?php

class N2SystemApplicationTypeBackend extends N2ApplicationType
{

    public $type = "backend";

    protected function autoload() {

        N2Loader::import('helpers.controllers.VisualManager', 'system.backend');
        N2Loader::import('helpers.controllers.VisualManagerAjax', 'system.backend');
    }

}