<?php

class N2SSPluginWidgetAutoplay extends N2PluginBase
{

    private static $group = 'autoplay';

    function onWidgetList(&$list) {
        $list[self::$group] = array(
            n2_('Autoplay'),
            $this->getPath(),
            3
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$group . DIRECTORY_SEPARATOR;
    }
}

N2Plugin::addPlugin('sswidget', 'N2SSPluginWidgetAutoplay');