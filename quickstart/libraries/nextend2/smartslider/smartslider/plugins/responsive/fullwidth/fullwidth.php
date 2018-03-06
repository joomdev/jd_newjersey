<?php

class N2SSPluginResponsiveFullWidth extends N2PluginBase {

    private static $name = 'fullwidth';

    function onResponsiveList(&$list, &$labels) {
        $list[self::$name]   = $this->getPath();
        $labels[self::$name] = n2_x('Fullwidth', 'Slider responsive mode');
    }

    static function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . self::$name . DIRECTORY_SEPARATOR;
    }
}

N2Plugin::addPlugin('ssresponsive', 'N2SSPluginResponsiveFullWidth');

class N2SSResponsiveFullWidth {

    private $params, $responsive;

    public function __construct($params, $responsive, $features) {
        $this->params           = $params;
        $this->responsive       = $responsive;
        $features->align->align = 'normal';

        $this->responsive->scaleDown = 1;
        $this->responsive->scaleUp   = 1;

        $this->responsive->minimumHeight = intval($this->params->get('responsiveSliderHeightMin', 0));
        $this->responsive->maximumHeight = intval($this->params->get('responsiveSliderHeightMax', 3000));


        if ($this->params->get('responsiveSlideWidth', 1)) {
            $this->responsive->maximumSlideWidth = intval($this->params->get('responsiveSlideWidthMax', 3000));
        }

        if ($this->params->get('responsiveSlideWidthDesktopLandscape', 0)) {
            $this->responsive->maximumSlideWidthLandscape = intval($this->params->get('responsiveSlideWidthMaxDesktopLandscape', 1600));
        }

        if ($this->params->get('responsiveSlideWidthTablet', 0)) {
            $this->responsive->maximumSlideWidthTablet = intval($this->params->get('responsiveSlideWidthMaxTablet', 980));
        }

        if ($this->params->get('responsiveSlideWidthTabletLandscape', 0)) {
            $this->responsive->maximumSlideWidthTabletLandscape = intval($this->params->get('responsiveSlideWidthMaxTabletLandscape', 1200));
        }

        if ($this->params->get('responsiveSlideWidthMobile', 0)) {
            $this->responsive->maximumSlideWidthMobile = intval($this->params->get('responsiveSlideWidthMaxMobile', 480));
        }

        if ($this->params->get('responsiveSlideWidthMobileLandscape', 0)) {
            $this->responsive->maximumSlideWidthMobileLandscape = intval($this->params->get('responsiveSlideWidthMaxMobileLandscape', 780));
        }

        $this->responsive->maximumSlideWidthConstrainHeight = intval($this->params->get('responsiveSlideWidthConstrainHeight', 0));

        $this->responsive->orientationMode = $this->params->get('responsiveSliderOrientation', 'width_and_height');

        $this->responsive->forceFull = intval($this->params->get('responsiveForceFull', 1));

        $this->responsive->forceFullOverflowX = $this->params->get('responsiveForceFullOverflowX', 'body');

        $this->responsive->forceFullHorizontalSelector = $this->params->get('responsiveForceFullHorizontalSelector', 'body');
    }
}