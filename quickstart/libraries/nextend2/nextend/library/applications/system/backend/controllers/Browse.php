<?php

class N2SystemBackendBrowseController extends N2BackendController
{

    public function __construct($appType, $defaultParams) {

        N2Localization::addJS(array(
            'Drop files anywhere to upload or',
            'Select files'
        ));

        parent::__construct($appType, $defaultParams);
    }

    public function actionIndex() {
        N2JS::addFirstCode("new NextendBrowse('" . $this->appType->router->createUrl('browse/index') . "', " . (defined('N2_IMAGE_UPLOAD_DISABLE') ? 0 : 1) . ");");
    }
}