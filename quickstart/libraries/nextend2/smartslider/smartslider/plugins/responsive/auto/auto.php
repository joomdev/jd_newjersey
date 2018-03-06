<?php

class N2SSPluginResponsiveAuto extends N2PluginBase {

    private static $name = 'auto';

    function onResponsiveList(&$list, &$labels) {
        $list[self::$name]   = $this->getPath();
        $labels[self::$name] = n2_x('Auto', 'Slider responsive mode');
    }

    static function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$name . DIRECTORY_SEPARATOR;
    }
}

N2Plugin::addPlugin('ssresponsive', 'N2SSPluginResponsiveAuto');

class N2SSResponsiveAuto {

    private $params, $responsive;

    public function __construct($params, $responsive, $features) {
        $this->params     = $params;
        $this->responsive = $responsive;

        $this->responsive->scaleDown = intval($this->params->get('responsiveScaleDown', 1));
        $this->responsive->scaleUp   = intval($this->params->get('responsiveScaleUp', 1));
        if ($this->responsive->scaleUp) {
            $features->align->align = 'normal';
        }


        $this->responsive->minimumHeight = intval($this->params->get('responsiveSliderHeightMin', 0));
        $this->responsive->maximumHeight = intval($this->params->get('responsiveSliderHeightMax', 3000));

        $this->responsive->maximumSlideWidth = intval($this->params->get('responsiveSlideWidthMax', 3000));

    }
}