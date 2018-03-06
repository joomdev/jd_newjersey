<?php

class N2SystemBackendSettingsView extends N2ViewBase
{

    public function renderGlobalConfigurationForm() {

        $values = N2Settings::getAll();
        $form   = new N2Form($this->appType);
        $form->loadArray($values);
        $form->loadXMLFile(N2Loader::getPath('models', 'system') . '/forms/globalconfig.xml');
        echo N2Html::openTag("form", array(
            "id"     => "nextend-config",
            "method" => "post",
            "action" => N2Request::getRequestUri()
        ));
        $form->render('global');
        echo N2Html::closeTag("form");

        N2JS::addFirstCode("
            new NextendForm(
                'nextend-config',
                '" . $this->appType->router->createAjaxUrl(array(
                'settings/index'
            )) . "',
                " . json_encode($values) . "
            );
        ");
    }

    /**
     * Generate action buttons to view
     *
     * @return array
     */
    public function getButtons() {
        $buttons = array();

        $buttons[] = array(
            'title'       => n2_('Clear cache'),
            'iconclass'   => 'nii nii-24x42 nii-global-action-icon nii-refresh',
            'htmlOptions' => array(
                'href' => $this->appType->router->createUrl(array(
                    'settings/clearcache'
                ))
            )
        );

        return $buttons;
    }

    public function renderAviaryConfigurationForm() {
        $values = N2ImageAviary::loadSettings();

        $form = new N2Form($this->appType);
        $form->loadArray($values);
        $form->loadXMLFile(N2Loader::getPath('models', 'system') . '/forms/aviary.xml');
        echo N2Html::openTag("form", array(
            "id"     => "nextend-config",
            "method" => "post",
            "action" => N2Request::getRequestUri()
        ));
        $form->render('aviary');
        echo N2Html::closeTag("form");
    }

    public function renderFontsConfigurationForm() {
        $values = N2Fonts::loadSettings();

        $form = new N2Form($this->appType);
        $form->loadArray($values);
        $form->loadArray($values['plugins']->toArray());
        $form->loadXMLFile(N2Loader::getPath('models', 'system') . '/forms/fonts.xml');
        echo N2Html::openTag("form", array(
            "id"     => "nextend-config",
            "method" => "post",
            "action" => N2Request::getRequestUri()
        ));
        $form->render('fonts');
        echo N2Html::closeTag("form");
    }

}