<?php

class N2SSPluginWidgetArrow extends N2PluginBase
{

    private static $group = 'arrow';

    function onWidgetList(&$list) {
        $list[self::$group] = array(
            n2_('Arrows'),
            $this->getPath(),
            1
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$group . DIRECTORY_SEPARATOR;
    }
}

N2Plugin::addPlugin('sswidget', 'N2SSPluginWidgetArrow');