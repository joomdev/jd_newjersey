<?php

class N2SmartsliderBackendSettingsController extends N2SmartSliderController
{

    public function initialize() {
        parent::initialize();

        N2Loader::import(array(
            'models.Settings',
            'models.Sliders'
        ), 'smartslider');
    }

    public function actionDefault() {

        if ($this->validatePermission('smartslider_config')) {

            if (N2Request::getInt('save')) {
                if ($this->validateToken()) {
                    $settingsModel = new N2SmartsliderSettingsModel();
                    if ($settingsModel->save()) {
                        $this->refresh();
                    }
                } else {
                    $this->refresh();
                }
            }

            $this->addView("../../inline/_sidebar_settings", array(), "sidebar");
            $this->addView('default', array(
                "action" => N2Request::getVar("nextendaction")
            ));
            $this->render();

        }
    }

    public function actionItemDefaults() {

        if ($this->validatePermission('smartslider_config')) {

            if (N2Request::getInt('save')) {
                if ($this->validateToken()) {
                    $settingsModel = new N2SmartsliderSettingsModel();
                    if ($settingsModel->saveDefaults(N2Request::getVar('defaults', array()))) {
                        $this->refresh();
                    }
                } else {
                    $this->refresh();
                }
            }

            $this->addView("../../inline/_sidebar_settings", array(), "sidebar");
            $this->addView("defaults");
            $this->render();

        }
    }

    public function actionJoomla() {
        //if (N2Platform::$isJoomla) $this->actionDefault('joomla');
    }

    public function actionClearCache() {
        if ($this->validatePermission('smartslider_config')) {
            if ($this->validateToken()) {
                $slidersModel = new N2SmartsliderSlidersModel();
                foreach ($slidersModel->_getAll() AS $slider) {
                    $slidersModel->refreshCache($slider['id']);
                }
                N2Cache::clearGroup('n2-ss-0');
                N2Cache::clearGroup('combined');
                N2Message::success(n2_('Cache cleared.'));
            }

            $this->redirect(array("settings/default"));
        }
    }

    public function actionAviary() {
        if ($this->validatePermission('nextend') && $this->validatePermission('nextend_config')) {
            $this->redirect(N2Base::getApplication('system')->getApplicationType('backend')->router->createUrl("settings/aviary"));
        }
    }

} 