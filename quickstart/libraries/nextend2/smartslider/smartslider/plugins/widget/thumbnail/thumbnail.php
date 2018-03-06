<?php

class N2SSPluginWidgetThumbnail extends N2PluginBase
{

    private static $group = 'thumbnail';

    function onWidgetList(&$list) {
        $list[self::$group] = array(
            n2_('Thumbnails'),
            $this->getPath(),
            6
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$group . DIRECTORY_SEPARATOR;
    }
}

N2Plugin::addPlugin('sswidget', 'N2SSPluginWidgetThumbnail');