<?php

class N2SmartsliderBackendSettingsView extends N2ViewBase
{

    public $xml;
    public $viewName = 'default';

    public function __set($name, $value) {
        if (!is_null($name)) {
            $this->$name = $value;
        }
    }

    private function _renderForm() {
        $settingsModel = new N2SmartsliderSettingsModel();
        $settingsModel->form($this->viewName);
        echo '<input name="namespace" value="' . $this->viewName . '" type="hidden" />';
    }

    public function _renderDefaultForm() {
        $this->viewName = 'default';
        $this->_renderForm();
    }

    public function renderDefaultsForm() {

        $settings = array(
            'font'  => array(),
            'style' => array()
        );
        N2Pluggable::doAction('smartsliderDefault', array(&$settings));

        $xmlString = '<root>';
        $this->defaultsAddTab($xmlString, $settings['font'], 'font', 'Font');
        $this->defaultsAddTab($xmlString, $settings['style'], 'style', 'Style');

        $xmlString .= '</root>';

        $form = new N2Form();
        $xml  = simplexml_load_string($xmlString);
        $form->setXML($xml);
        $form->render('defaults');
    }

    public function defaultsAddTab(&$xml, $settings, $key, $label) {
        $xml .= '<params name="' . $key . '" label="' . $label . '"><param type="token"/>';
        foreach ($settings AS $field) {
            $xml .= $field;
        }
        $xml .= '</params>';
    }
}