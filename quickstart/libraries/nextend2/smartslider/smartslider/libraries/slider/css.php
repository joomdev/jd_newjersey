<?php

N2Loader::import('libraries.parse.font');
N2Loader::import('libraries.parse.style');

abstract class N2SmartSliderCSSAbstract {

    /**
     * @var N2SmartSliderAbstract
     */
    protected $slider;

    public $sizes = array();

    public function __construct($slider) {
        $this->slider = $slider;
    }

    public function render() {
        $slider = $this->slider;
        $params = $slider->params;

        $width  = intval($params->get('width', 900));
        $height = intval($params->get('height', 500));
        if ($width < 10) {
            N2Message::error(n2_('Slider width is not valid number!'));
        }
        if ($height < 10) {
            N2Message::error(n2_('Slider height is not valid number!'));
        }
        $context = array(
            'id'             => "~'#{$slider->elementId}'",
            'width'          => $width . 'px',
            'height'         => $height . 'px',
            'canvas'         => 0,
            'count'          => count($slider->slides),
            'margin'         => '0px 0px 0px 0px',
            'clear'          => 'clear.n2less',
            'hasPerspective' => 0
        );

        $perspective = intval($params->get('perspective', 1500));
        if ($perspective > 0) {
            $context['hasPerspective'] = 1;
            $context['perspective']    = $perspective . 'px';
        }

        $this->renderType($context);

        if ($params->get('imageload', 0)) {
            N2LESS::addFile(NEXTEND_SMARTSLIDER_ASSETS . '/less/spinner.n2less', $slider->cacheId, $context, NEXTEND_SMARTSLIDER_ASSETS . '/less' . NDS);
        }

        $this->sizes['marginVertical']   = 0;
        $this->sizes['marginHorizontal'] = 0;

        $this->sizes['width']        = intval($context['width']);
        $this->sizes['height']       = intval($context['height']);
        $this->sizes['canvasWidth']  = intval($context['canvaswidth']);
        $this->sizes['canvasHeight'] = intval($context['canvasheight']);
    }

    protected abstract function renderType(&$context);

    protected function setContextFonts($matches, &$context, $fonts, $value) {
        $context['font' . $fonts] = '~".' . $matches[0] . '"';

        $font                              = new N2ParseFont($value);
        $context['font' . $fonts . 'text'] = '";' . $font->printTab() . '"';
        $font->mixinTab('Link');
        $context['font' . $fonts . 'link'] = '";' . $font->printTab('Link') . '"';
        $font->mixinTab('Link:Hover', 'Link');
        $context['font' . $fonts . 'hover'] = '";' . $font->printTab('Link:Hover') . '"';
    }

    protected function setContextStyles($selector, &$context, $styles, $value) {
        $context['style' . $styles] = '~".' . $selector . '"';

        $style                                 = new N2ParseStyle($value);
        $context['style' . $styles . 'normal'] = '";' . $style->printTab('Normal') . '"';
        $context['style' . $styles . 'hover']  = '";' . $style->printTab('Hover') . '"';

    }

}