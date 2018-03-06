<?php


N2Loader::import("libraries.ajax.ajax");
N2Loader::import('libraries.form.form');

class N2SmartSliderAjaxModel extends N2Ajax
{

    public function sliderType($appType) {
        $configurationXmlFile = dirname(__FILE__) . '/forms/slider.xml';

        $values         = N2Request::getVar('values', array());
        $values['type'] = N2Post::getVar('value');

        return $this->subform($appType, $configurationXmlFile, $values, 'slider', 'type');
    }

    public function sliderResponsiveMode($appType) {
        $configurationXmlFile = dirname(__FILE__) . '/forms/slider.xml';

        $values                    = N2Request::getVar('values', array());
        $values['responsive-mode'] = N2Post::getVar('value');

        return $this->subform($appType, $configurationXmlFile, $values, 'slider', 'responsive-mode');
    }

    private function getWidgetPath($name) {
        $list = array();
        N2Plugin::callPlugin('sswidget', 'onWidgetList', array(&$list));
        if (isset($list[$name])) {
            return $list[$name][1];
        }

        return false;
    }

    public function sliderWidget($appType, $name) {

        $configurationXmlFile = $this->getWidgetPath($name) . 'config.xml';

        $values                   = (array)N2Request::getVar('values', array());
        $values['widget' . $name] = N2Post::getVar('value');

        $class = 'N2SSPluginWidget' . $name . N2Post::getVar('value');
        if (class_exists($class, false)) {
            $values = array_merge(call_user_func(array(
                $class,
                'getDefaults'
            )), $values);
        }

        return $this->subform($appType, $configurationXmlFile, $values, 'slider', 'widget' . $name);
    }
}