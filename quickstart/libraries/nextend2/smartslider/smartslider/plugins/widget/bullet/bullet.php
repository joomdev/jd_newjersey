<?php

class N2SSPluginWidgetBullet extends N2PluginBase
{

    private static $group = 'bullet';

    function onWidgetList(&$list) {
        $list[self::$group] = array(
            n2_('Bullets'),
            $this->getPath(),
            2
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$group . DIRECTORY_SEPARATOR;
    }
}

N2Plugin::addPlugin('sswidget', 'N2SSPluginWidgetBullet');