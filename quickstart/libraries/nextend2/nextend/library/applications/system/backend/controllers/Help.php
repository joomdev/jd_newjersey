<?php

class N2SystemBackendHelpController extends N2BackendController
{
    public $layoutName = 'full';

    public function actionIndex() {
        $this->addView("index");
        $this->render();
    }

} 