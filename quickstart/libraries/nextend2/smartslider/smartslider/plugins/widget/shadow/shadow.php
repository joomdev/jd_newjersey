<?php

class N2SSPluginWidgetShadow extends N2PluginBase
{

    private static $group = 'shadow';

    function onWidgetList(&$list) {
        $list[self::$group] = array(
            n2_('Shadows'),
            $this->getPath(),
            7
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$group . DIRECTORY_SEPARATOR;
    }
}

N2Plugin::addPlugin('sswidget', 'N2SSPluginWidgetShadow');