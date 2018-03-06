<?php

N2Loader::import('libraries.plugins.N2SliderWidgetAbstract', 'smartslider');
N2Loader::import('libraries.image.color');

class N2SSPluginWidgetBulletTransition extends N2SSPluginWidgetAbstract {

    var $_name = 'transition';

    private static $key = 'widget-bullet-';

    static function getDefaults() {
        return array(
            'widget-bullet-position-mode'        => 'simple',
            'widget-bullet-position-area'        => 10,
            'widget-bullet-position-offset'      => 10,
            'widget-bullet-action'               => 'click',
            'widget-bullet-style'                => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwYWIiLCJwYWRkaW5nIjoiNXwqfDV8Knw1fCp8NXwqfHB4IiwiYm94c2hhZG93IjoiMHwqfDB8KnwwfCp8MHwqfDAwMDAwMGZmIiwiYm9yZGVyIjoiMHwqfHNvbGlkfCp8MDAwMDAwZmYiLCJib3JkZXJyYWRpdXMiOiI1MCIsImV4dHJhIjoibWFyZ2luOiA0cHg7In0seyJiYWNrZ3JvdW5kY29sb3IiOiIwMGMxYzRmZiJ9XX0=',
            'widget-bullet-bar'                  => '',
            'widget-bullet-align'                => 'center',
            'widget-bullet-orientation'          => 'auto',
            'widget-bullet-bar-full-size'        => 0,
            'widget-bullet-overlay'              => 0,
            'widget-bullet-thumbnail-show-image' => 0,
            'widget-bullet-thumbnail-width'      => 100,
            'widget-bullet-thumbnail-width'      => 60,
            'widget-bullet-thumbnail-style'      => 'eyJuYW1lIjoiU3RhdGljIiwiZGF0YSI6W3siYmFja2dyb3VuZGNvbG9yIjoiMDAwMDAwODAiLCJwYWRkaW5nIjoiM3wqfDN8KnwzfCp8M3wqfHB4IiwiYm94c2hhZG93IjoiMHwqfDB8KnwwfCp8MHwqfDAwMDAwMGZmIiwiYm9yZGVyIjoiMHwqfHNvbGlkfCp8MDAwMDAwZmYiLCJib3JkZXJyYWRpdXMiOiIzIiwiZXh0cmEiOiJtYXJnaW46IDVweDsifV19',
            'widget-bullet-thumbnail-side'       => 'before'
        );
    }

    function onBulletList(&$list) {
        $list[$this->_name] = $this->getPath();
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'transition' . DIRECTORY_SEPARATOR;
    }

    static function getPositions(&$params) {
        $positions                    = array();
        $positions['bullet-position'] = array(
            self::$key . 'position-',
            'bullet'
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
		if (count($slider->slides) <= 1) {
			return '';
		}

        N2LESS::addFile(N2Filesystem::translate(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'transition' . DIRECTORY_SEPARATOR . 'style.n2less'), $slider->cacheId, array(
            "sliderid" => $slider->elementId
        ), NEXTEND_SMARTSLIDER_ASSETS . '/less' . NDS);
        N2JS::addFile(N2Filesystem::translate(dirname(__FILE__) . '/transition/bullet.min.js'), $id);
    

        list($displayClass, $displayAttributes) = self::getDisplayAttributes($params, self::$key);

        $bulletStyle = N2StyleRenderer::render($params->get(self::$key . 'style'), 'dot', $slider->elementId, 'div#' . $slider->elementId . ' ');
        $barStyle    = N2StyleRenderer::render($params->get(self::$key . 'bar'), 'simple', $slider->elementId, 'div#' . $slider->elementId . ' ');

        list($style, $attributes) = self::getPosition($params, self::$key);
        $attributes['data-offset'] = $params->get(self::$key . 'position-offset', 0);

        $dots = array();        
        for ($i = 0; $i < count($slider->slides); $i++) {
            $dots[] = N2Html::tag('div', array(
                'class'    => 'n2-ow ' . $bulletStyle,
                'tabindex' => '0'
            ), '');
        }

        $orientation = self::getOrientationByPosition($params->get(self::$key . 'position-mode'), $params->get(self::$key . 'position-area'), $params->get(self::$key . 'orientation'));
        if ($orientation == 'auto') {
            $orientation = 'horizontal';
        }
        $html = '';
        switch ($orientation) {
            case 'vertical':
                $html .= implode('<br>', $dots);
                break;
            default:
                $html .= implode('', $dots);
        }

        $parameters = array(
            'overlay' => $params->get(self::$key . 'position-mode') != 'simple' || $params->get(self::$key . 'overlay'),
            'area'    => intval($params->get(self::$key . 'position-area'))
        );

        $thumbnails = array();
        if ($params->get(self::$key . 'thumbnail-show-image')) {
            foreach ($slider->slides AS $slide) {
                $thumbnails[] = $slide->getThumbnail();
            }
            $parameters['thumbnailWidth']  = intval($params->get(self::$key . 'thumbnail-width'));
            $parameters['thumbnailHeight'] = intval($params->get(self::$key . 'thumbnail-height'));
            $parameters['thumbnailStyle']  = N2StyleRenderer::render($params->get(self::$key . 'thumbnail-style'), 'simple', $slider->elementId, '');
            $side                          = $params->get(self::$key . 'thumbnail-side');


            if ($side == 'before') {
                if ($orientation == 'vertical') {
                    $position = 'left';
                } else {
                    $position = 'top';
                }
            } else {
                if ($orientation == 'vertical') {
                    $position = 'right';
                } else {
                    $position = 'bottom';
                }
            }
            $parameters['thumbnailPosition'] = $position;
        }
        $parameters['thumbnails'] = $thumbnails;
        $parameters['action']     = $params->get(self::$key . 'action');
        $parameters['numeric']    = 0;

        N2JS::addInline('new N2Classes.SmartSliderWidgetBulletTransition("' . $id . '", ' . json_encode($parameters) . ');');

        $fullSize = intval($params->get(self::$key . 'bar-full-size'));
        if ($fullSize) {
            $barStyle .= " n2-bullet-bar-full-size";
        }

        return N2Html::tag("div", $displayAttributes + $attributes + array(
                "class" => $displayClass . ' n2-ss-control-bullet',
                "style" => $style
            ), N2HTML::tag("div", array(
            "class" => $barStyle . " nextend-bullet-bar n2-ow nextend-bullet-bar-" . $orientation,
            "style" => "text-align: " . $params->get(self::$key . 'align') . ";"
        ), $html));
    }

    public static function prepareExport($export, $params) {
        $export->addVisual($params->get(self::$key . 'style'));
        $export->addVisual($params->get(self::$key . 'bar'));
    }

    public static function prepareImport($import, $params) {

        $params->set(self::$key . 'style', $import->fixSection($params->get(self::$key . 'style')));
        $params->set(self::$key . 'bar', $import->fixSection($params->get(self::$key . 'bar')));
    }

}

N2Plugin::addPlugin('sswidgetbullet', 'N2SSPluginWidgetBulletTransition');