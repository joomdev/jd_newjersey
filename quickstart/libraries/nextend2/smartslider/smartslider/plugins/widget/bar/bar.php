<?php

class N2SSPluginWidgetBar extends N2PluginBase
{

    private static $group = 'bar';

    function onWidgetList(&$list) {
        $list[self::$group] = array(
            n2_('Text Bar'),
            $this->getPath(),
            5
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$group . DIRECTORY_SEPARATOR;
    }
}

N2Plugin::addPlugin('sswidget', 'N2SSPluginWidgetBar');