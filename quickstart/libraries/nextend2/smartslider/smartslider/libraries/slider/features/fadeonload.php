<?php

class N2SmartSliderFeatureFadeOnLoad {

    private $slider;

    public $fadeOnLoad = 1;

    public $fadeOnScroll = 0;

    public $playWhenVisible = 1;

    public $playWhenVisibleAt = 0.5;

    public function __construct($slider) {

        $this->slider = $slider;

        $this->fadeOnLoad        = intval($slider->params->get('fadeOnLoad', 1));
        $this->fadeOnScroll      = intval($slider->params->get('fadeOnScroll', 0));
        $this->playWhenVisible   = intval($slider->params->get('playWhenVisible', 1));
        $this->playWhenVisibleAt = max(0, min(100, intval($slider->params->get('playWhenVisibleAt', 50)))) / 100;

        if (!empty($this->fadeOnScroll) && $this->fadeOnScroll) {
            $this->fadeOnLoad   = 1;
            $this->fadeOnScroll = 1;
        } else {
            $this->fadeOnScroll = 0;
        }
    }

    public function forceFadeOnLoad() {
        if (!$this->fadeOnScroll && !$this->fadeOnLoad) {
            $this->fadeOnLoad = 1;
        }
    }

    public function getSliderClass() {
        if ($this->fadeOnLoad) {
            return 'n2-ss-load-fade ';
        }

        return '';
    }

    public function renderPlaceholder($sizes) {

        if (!$this->slider->isAdmin && $this->fadeOnLoad && ($this->slider->features->responsive->scaleDown || $this->slider->features->responsive->scaleUp)) {

            if (N2SystemHelper::testMemoryLimit()) {
                if ($sizes['width'] + $sizes['marginHorizontal'] > 0 && $sizes['height'] > 0 && function_exists('imagecreatetruecolor')) {
                    return N2Html::tag("div", array(
                        "id"     => $this->slider->elementId . "-placeholder",
                        "encode" => false,
                        "style"  => 'position: relative;z-index:2;color:RGBA(0,0,0,0);'
                    ), $this->makeImage($sizes));
                } else {
                    N2CSS::addCode("#{$this->slider->elementId} .n2-ss-load-fade{position: relative !important;}", $this->slider->cacheId);
                }

            } else {
                N2Message::error(n2_("It seems like the <a href='http://php.net/manual/en/ini.core.php#ini.memory-limit'>memory_limit</a> on the server is too low for the fade on load feature. Please set it minimum 60M and reload the page!"));
            }
        } else {
            N2CSS::addCode("#{$this->slider->elementId}.n2-ss-load-fade{position: relative !important;}", $this->slider->cacheId);
        }

        return '';
    }

    public function makeJavaScriptProperties(&$properties) {
        $properties['load']              = array(
            'fade'   => $this->fadeOnLoad,
            'scroll' => ($this->fadeOnScroll & !$this->slider->isAdmin)
        );
        $properties['playWhenVisible']   = $this->playWhenVisible;
        $properties['playWhenVisibleAt'] = $this->playWhenVisibleAt;
    }


    private function makeImage($sizes) {
        $html = N2Html::image("data:image/svg+xml;base64," . $this->transparentImage($sizes['width'] + $sizes['marginHorizontal'], $sizes['height']), 'Slider', array(
            'style' => 'width: 100%; max-width:' . ($this->slider->features->responsive->maximumSlideWidth + $sizes['marginHorizontal']) . 'px; display: block;',
            'class' => 'n2-ow'
        ));

        if ($sizes['marginVertical'] > 0) {
            $html .= N2Html::image("data:image/svg+xml;base64," . $this->transparentImage($sizes['width'] + $sizes['marginHorizontal'], $sizes['marginVertical']), 'Slider', array(
                'style' => 'width: 100%;',
                'class' => 'n2-ow'
            ));
        }

        return $html;
    }

    private function transparentImage($width, $height) {

        return n2_base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.0" width="' . $width . '" height="' . $height . '" ></svg>');
    }

    private static function gcd($a, $b) {
        return ($a % $b) ? self::gcd($b, $a % $b) : $b;
    }
}