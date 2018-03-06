<?php

N2Loader::import('libraries.plugins.N2SliderWidgetAbstract', 'smartslider');
N2Loader::import('libraries.image.color');

class N2SSPluginWidgetBarHorizontal extends N2SSPluginWidgetAbstract {

    private static $key = 'widget-bar-';

    var $_name = 'horizontal';

    static function getDefaults() {
        return array(
            'widget-bar-position-mode'    => 'simple',
            'widget-bar-position-area'    => 10,
            'widget-bar-position-offset'  => 30,
            'widget-bar-style'            => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwYWIiLCJwYWRkaW5nIjoiNXwqfDIwfCp8NXwqfDIwfCp8cHgiLCJib3hzaGFkb3ciOiIwfCp8MHwqfDB8KnwwfCp8MDAwMDAwZmYiLCJib3JkZXIiOiIwfCp8c29saWR8KnwwMDAwMDBmZiIsImJvcmRlcnJhZGl1cyI6IjQwIiwiZXh0cmEiOiIifV19',
            'widget-bar-show-title'       => 1,
            'widget-bar-font-title'       => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siY29sb3IiOiJmZmZmZmZmZiIsInNpemUiOiIxNHx8cHgiLCJ0c2hhZG93IjoiMHwqfDB8KnwwfCp8MDAwMDAwYzciLCJhZm9udCI6Ik1vbnRzZXJyYXQiLCJsaW5laGVpZ2h0IjoiMS4zIiwiYm9sZCI6MCwiaXRhbGljIjowLCJ1bmRlcmxpbmUiOjAsImFsaWduIjoibGVmdCIsImV4dHJhIjoidmVydGljYWwtYWxpZ246IG1pZGRsZTsifSx7ImNvbG9yIjoiZmMyODI4ZmYiLCJhZm9udCI6Imdvb2dsZShAaW1wb3J0IHVybChodHRwOi8vZm9udHMuZ29vZ2xlYXBpcy5jb20vY3NzP2ZhbWlseT1SYWxld2F5KTspLEFyaWFsIiwic2l6ZSI6IjI1fHxweCJ9LHt9XX0=',
            'widget-bar-show-description' => 1,
            'widget-bar-font-description' => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siY29sb3IiOiJmZmZmZmZmZiIsInNpemUiOiIxNHx8cHgiLCJ0c2hhZG93IjoiMHwqfDB8KnwwfCp8MDAwMDAwYzciLCJhZm9udCI6Ik1vbnRzZXJyYXQiLCJsaW5laGVpZ2h0IjoiMS4zIiwiYm9sZCI6MCwiaXRhbGljIjoxLCJ1bmRlcmxpbmUiOjAsImFsaWduIjoibGVmdCIsImV4dHJhIjoidmVydGljYWwtYWxpZ246IG1pZGRsZTsifSx7ImNvbG9yIjoiZmMyODI4ZmYiLCJhZm9udCI6Imdvb2dsZShAaW1wb3J0IHVybChodHRwOi8vZm9udHMuZ29vZ2xlYXBpcy5jb20vY3NzP2ZhbWlseT1SYWxld2F5KTspLEFyaWFsIiwic2l6ZSI6IjI1fHxweCJ9LHt9XX0=',
            'widget-bar-width'            => '100%',
            'widget-bar-full-width'       => 0,
            'widget-bar-overlay'          => 0,
            'widget-bar-separator'        => ' - ',
            'widget-bar-align'            => 'center',
            'widget-bar-animate'          => 0
        );
    }

    function onBarList(&$list) {
        $list[$this->_name] = $this->getPath();
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'horizontal' . DIRECTORY_SEPARATOR;
    }

    static function getPositions(&$params) {
        $positions = array();

        $positions['bar-position'] = array(
            self::$key . 'position-',
            'bar'
        );

        return $positions;
    }

    /**
     * @param $slider N2SmartSliderAbstract
     * @param $id
     * @param $params
     *
     * @return string
     */
    static function render($slider, $id, $params) {

        N2LESS::addFile(N2Filesystem::translate(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'horizontal' . DIRECTORY_SEPARATOR . 'style.n2less'), $slider->cacheId, array(
            "sliderid" => $slider->elementId
        ), NEXTEND_SMARTSLIDER_ASSETS . '/less' . NDS);
        N2JS::addFile(N2Filesystem::translate(dirname(__FILE__) . '/horizontal/bar.min.js'), $id);
    

        list($displayClass, $displayAttributes) = self::getDisplayAttributes($params, self::$key);

        $styleClass      = N2StyleRenderer::render($params->get(self::$key . 'style'), 'simple', $slider->elementId, 'div#' . $slider->elementId . ' ');
        $fontTitle       = N2FontRenderer::render($params->get(self::$key . 'font-title'), 'simple', $slider->elementId, 'div#' . $slider->elementId . ' ', $slider->fontSize);
        $fontDescription = N2FontRenderer::render($params->get(self::$key . 'font-description'), 'simple', $slider->elementId, 'div#' . $slider->elementId . ' ', $slider->fontSize);

        list($style, $attributes) = self::getPosition($params, self::$key);
        $attributes['data-offset'] = $params->get(self::$key . 'position-offset');

        $style .= 'text-align: ' . $params->get(self::$key . 'align') . ';';

        $width = $params->get(self::$key . 'width');
        if (is_numeric($width) || substr($width, -1) == '%' || substr($width, -2) == 'px') {
            $style .= 'width:' . $width . ';';
        } else {
            $attributes['data-sswidth'] = $width;
        }

        $innerStyle = '';
        if (!$params->get(self::$key . 'full-width')) {
            $innerStyle = 'display: inline-block;';
        }

        $separator       = $params->get(self::$key . 'separator');
        $showTitle       = intval($params->get(self::$key . 'show-title'));
        $showDescription = intval($params->get(self::$key . 'show-description'));
        $slides          = array();
        for ($i = 0; $i < count($slider->slides); $i++) {

            $html = '';
            if ($showTitle) {
                $html .= N2Html::tag('span', array(
                    'class' => $fontTitle . ' n2-ow'
                ), N2Translation::_($slider->slides[$i]->getTitle()));
            }

            $description = $slider->slides[$i]->getDescription();
            if ($showDescription && !empty($description)) {
                $html .= N2Html::tag('span', array('class' => $fontDescription . ' n2-ow'), (!empty($html) ? $separator : '') . N2Translation::_($description));
            }

            $slides[$i] = array(
                'html'    => $html,
                'hasLink' => $slider->slides[$i]->hasLink
            );
        }

        $parameters = array(
            'overlay' => $params->get(self::$key . 'position-mode') != 'simple' || $params->get(self::$key . 'overlay'),
            'area'    => intval($params->get(self::$key . 'position-area')),
            'animate' => intval($params->get(self::$key . 'animate'))
        );

        N2JS::addInline('new N2Classes.SmartSliderWidgetBarHorizontal("' . $id . '", ' . json_encode($slides) . ', ' . json_encode($parameters) . ');');

        return N2Html::tag("div", $displayAttributes + $attributes + array(
                "class" => $displayClass . "nextend-bar nextend-bar-horizontal n2-ow",
                "style" => $style
            ), N2Html::tag("div", array(
            "class" => $styleClass . ' n2-ow',
            "style" => $innerStyle . ($slides[$slider->firstSlideIndex]['hasLink'] ? 'cursor:pointer;' : '')
        ), $slides[$slider->firstSlideIndex]['html']));
    }

    public static function prepareExport($export, $params) {
        $export->addVisual($params->get(self::$key . 'style'));
        $export->addVisual($params->get(self::$key . 'font-title'));
        $export->addVisual($params->get(self::$key . 'font-description'));
    }

    public static function prepareImport($import, $params) {

        $params->set(self::$key . 'style', $import->fixSection($params->get(self::$key . 'style', '')));
        $params->set(self::$key . 'font-title', $import->fixSection($params->get(self::$key . 'font-title', '')));
        $params->set(self::$key . 'font-description', $import->fixSection($params->get(self::$key . 'font-description', '')));
    }
}

class N2SSPluginWidgetBarHorizontalFull extends N2SSPluginWidgetBarHorizontal {

    var $_name = 'horizontalFull';

    static function getDefaults() {
        return array_merge(N2SSPluginWidgetBarHorizontal::getDefaults(), array(
            'widget-bar-position-offset' => 0,
            'widget-bar-style'           => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwYWIiLCJwYWRkaW5nIjoiMjB8KnwyMHwqfDIwfCp8MjB8KnxweCIsImJveHNoYWRvdyI6IjB8KnwwfCp8MHwqfDB8KnwwMDAwMDBmZiIsImJvcmRlciI6IjB8Knxzb2xpZHwqfDAwMDAwMGZmIiwiYm9yZGVycmFkaXVzIjoiMCIsImV4dHJhIjoiIn1dfQ==',
            'widget-bar-full-width'      => 1,
            'widget-bar-align'           => 'left'
        ));
    }
}

N2Plugin::addPlugin('sswidgetbar', 'N2SSPluginWidgetBarHorizontalFull');