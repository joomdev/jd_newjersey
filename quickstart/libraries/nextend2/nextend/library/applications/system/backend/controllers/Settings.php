<?php

class N2SystemBackendSettingsController extends N2BackendController
{

    protected function initLayout() {
        if (N2Request::getVar('layout') == 'modal') {
            $this->layoutName = 'modal';
        }
        parent::initLayout();
    }

    public function actionIndex() {
        if ($this->canDo('nextend_config')) {

            $data = N2Post::getVar('global');
            if (is_array($data)) {
                if ($this->validateToken()) {
                    N2Settings::setAll($data);
                } else {
                    $this->refresh();
                }
            }


            $this->addView("../../inline/sidebar/settings", array(
                "appObj" => $this
            ), "sidebar");

            $this->addView("index");
            $this->render();
        } else {
            $this->noAccess();
        }
    }

    public function actionAviary() {
        if ($this->canDo('nextend_config')) {
            N2Loader::import('libraries.image.aviary');
            $aviary = N2Request::getVar('aviary', false);
            if ($aviary) {
                if ($this->validateToken()) {
                    N2ImageAviary::storeSettings($aviary);
                    N2Message::success(n2_('Saved.'));
                    N2Request::redirect($this->appType->router->createUrl(array(
                        "settings/aviary",
                        array(
                            'layout' => N2Request::getCmd('layout', '')
                        )
                    )));
                } else {
                    $this->refresh();
                }
            }

            $this->addView("../../inline/sidebar/settings", array(
                "appObj" => $this
            ), "sidebar");

            $this->addView("aviary");
            $this->render();
        }
    }

    public function actionFonts() {
        if ($this->canDo('nextend_config')) {
            $fonts = N2Request::getVar('fonts', false);
            if ($fonts) {
                if ($this->validateToken()) {
                    N2Fonts::storeSettings($fonts);
                    N2Message::success(n2_('Saved.'));
                    N2Request::redirect($this->appType->router->createUrl(array("settings/fonts")));
                } else {
                    $this->refresh();
                }
            }

            $this->addView("../../inline/sidebar/settings", array(
                "appObj" => $this
            ), "sidebar");

            $this->addView("fonts");
            $this->render();
        }
    }

} 