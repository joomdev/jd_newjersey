<?php

class N2SystemBackendAnimationControllerAjax extends N2SystemBackendVisualManagerControllerAjax
{
    protected $type = 'animation';

    public function getModel() {
        return new N2SystemAnimationModel();
    }
}