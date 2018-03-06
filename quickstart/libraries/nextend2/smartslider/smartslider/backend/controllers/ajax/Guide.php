<?php

class N2SmartsliderBackendGuideControllerAjax extends N2SmartSliderControllerAjax
{

    public function actionEnd() {

        $this->validateToken();

        $this->validatePermission('smartslider_edit');

        $key = N2Request::getCmd('key');
        N2SmartSliderSettings::set('guide-' . $key, 0);

        N2Message::notice('The ' . $key . ' guide completed. If you need it again, you can turn it on in the "Settings"!');

        $this->response->respond();
    }
}