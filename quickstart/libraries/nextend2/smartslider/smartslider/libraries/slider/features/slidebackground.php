<?php

class N2SmartSliderFeatureSlideBackground {

    private $slider;

    public function __construct($slider) {

        $this->slider = $slider;
    }

    public function makeJavaScriptProperties(&$properties) {
        $properties['background.parallax.tablet'] = intval($this->slider->params->get('bg-parallax-tablet', 0));
        $properties['background.parallax.mobile'] = intval($this->slider->params->get('bg-parallax-mobile', 0));
    }

    /**
     * @param $slide N2SmartSliderSlide
     *
     * @return string
     */

    public function make($slide) {


        if ($slide->parameters->get('background-type') == '') {
            $slide->parameters->set('background-type', 'color');
            if ($slide->parameters->get('backgroundVideoMp4')) {
                $slide->parameters->set('background-type', 'video');
            } else if ($slide->parameters->get('backgroundImage')) {
                $slide->parameters->set('background-type', 'image');
            }
        }

        $html = $this->makeBackground($slide);

        return $html;
    }

    private function getBackgroundStyle($slide) {
        $style    = '';
        $color    = $slide->parameters->get('backgroundColor', '');
        $gradient = $slide->parameters->get('backgroundGradient', 'off');

        if (!class_exists('N2Color')) {
            N2Loader::import("libraries.image.color");
        }

        if ($gradient != 'off') {
            $colorEnd = $slide->parameters->get('backgroundColorEnd', 'ffffff00');
            switch ($gradient) {
                case 'horizontal':
                    $style .= 'background:#' . substr($color, 0, 6) . ';';
                    $style .= 'background:-moz-linear-gradient(left, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    $style .= 'background:-webkit-linear-gradient(left, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    $style .= 'background:linear-gradient(to right, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    $style .= 'background:filter: progid:DXImageTransform.Microsoft.gradient( startColorstr=\'#' . substr($color, 0, 6) . '\', endColorstr=\'#' . substr($color, 0, 6) . '\',GradientType=1);';
                    break;
                case 'vertical':
                    $style .= 'background:#' . substr($color, 0, 6) . ';';
                    $style .= 'background:-moz-linear-gradient(top, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    $style .= 'background:-webkit-linear-gradient(top, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    $style .= 'background:linear-gradient(to bottom, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    $style .= 'background:filter: progid:DXImageTransform.Microsoft.gradient( startColorstr=\'#' . substr($color, 0, 6) . '\', endColorstr=\'#' . substr($color, 0, 6) . '\',GradientType=0);';
                    break;
                case 'diagonal1':
                    $style .= 'background:#' . substr($color, 0, 6) . ';';
                    $style .= 'background:-moz-linear-gradient(45deg, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    $style .= 'background:-webkit-linear-gradient(45deg, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    $style .= 'background:linear-gradient(45deg, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorEnd) . ' 100%);';
                    $style .= 'background:filter: progid:DXImageTransform.Microsoft.gradient( startColorstr=\'#' . substr($color, 0, 6) . '\', endColorstr=\'#' . substr($color, 0, 6) . '\',GradientType=1);';
                    break;
                case 'diagonal2':
                    $style .= 'background:#' . substr($color, 0, 6) . ';';
                    $style .= 'background:-moz-linear-gradient(-45deg, ' . N2Color::colorToRGBA($colorEnd) . ' 0%,' . N2Color::colorToRGBA($color) . ' 100%);';
                    $style .= 'background:-webkit-linear-gradient(-45deg, ' . N2Color::colorToRGBA($colorEnd) . ' 0%,' . N2Color::colorToRGBA($color) . ' 100%);';
                    $style .= 'background:linear-gradient(-45deg, ' . N2Color::colorToRGBA($colorEnd) . ' 0%,' . N2Color::colorToRGBA($color) . ' 100%);';
                    $style .= 'background:filter: progid:DXImageTransform.Microsoft.gradient( startColorstr=\'#' . substr($color, 0, 6) . '\', endColorstr=\'#' . substr($color, 0, 6) . '\',GradientType=1);';
                    break;
            }
        } else {
            if (strlen($color) == 8 && substr($color, 6, 2) != '00') {
                $style = 'background-color: #' . substr($color, 0, 6) . ';';
                $style .= "background-color: " . N2Color::colorToRGBA($color) . ";";
            }
        }

        return $style;
    }

    private function makeBackground($slide) {

        $videoHTML      = '';
        $backgroundType = $slide->parameters->get('background-type');

        if ($backgroundType == 'video') {
            $videoHTML .= $this->makeBackgroundVideo($slide);
        }

        $backgroundColorStyle = $this->getBackgroundStyle($slide);

        $backgroundImageOpacity = min(100, max(0, $slide->parameters->get('backgroundImageOpacity', 100))) / 100;

        $backgroundImageBlur = max(0, $slide->parameters->get('backgroundImageBlur', 0));

        $x = max(0, min(100, $slide->parameters->get('backgroundFocusX', 50)));
        $y = max(0, min(100, $slide->parameters->get('backgroundFocusY', 50)));

        $fillMode = $slide->parameters->get('backgroundMode', 'default');
        if ($fillMode == 'default') {
            $fillMode = $this->slider->params->get('backgroundMode', 'fill');
        }

        if ($backgroundType == 'color') {
            return $this->colorOnly($videoHTML, $fillMode, $backgroundColorStyle, $backgroundImageOpacity, $backgroundImageBlur, $x, $y);
        }

        $rawBackgroundImage = $slide->parameters->get('backgroundImage', '');

        if (empty($rawBackgroundImage)) {
            if (empty($backgroundColorStyle)) {
                return $this->emptyBackgroundImage($videoHTML);
            } else {
                return $this->colorOnly($videoHTML, $fillMode, $backgroundColorStyle, $backgroundImageOpacity, $backgroundImageBlur, $x, $y);
            }
        }

        if ($slide->hasGenerator()) {

            $backgroundImage = $slide->fill($rawBackgroundImage);

            $imageData = N2ImageManager::getImageData($rawBackgroundImage);

            $imageData['desktop-retina']['image'] = $slide->fill($imageData['desktop-retina']['image']);
            $imageData['tablet']['image']         = $slide->fill($imageData['tablet']['image']);
            $imageData['tablet-retina']['image']  = $slide->fill($imageData['tablet-retina']['image']);
            $imageData['mobile']['image']         = $slide->fill($imageData['mobile']['image']);
            $imageData['mobile-retina']['image']  = $slide->fill($imageData['mobile-retina']['image']);
        } else {
            $backgroundImage = $slide->fill($rawBackgroundImage);
            $imageData       = N2ImageManager::getImageData($backgroundImage);
        }

        if (empty($backgroundImage)) {
            $src = N2Image::base64Transparent();
        } else {
            $src = N2ImageHelper::dynamic($this->slider->features->optimize->optimizeBackground($backgroundImage, $x, $y));
        }


        $alt   = $slide->fill($slide->parameters->get('backgroundAlt', ''));
        $title = $slide->fill($slide->parameters->get('backgroundTitle', ''));

        return $this->image($videoHTML, $fillMode, $backgroundColorStyle, $backgroundImageOpacity, $backgroundImageBlur, $src, $imageData, $alt, $title, $x, $y);

    }

    private function emptyBackgroundImage($videoHTML) {

        return N2Html::tag('div', array(
            "class" => "n2-ss-slide-background n2-ow"
        ), N2Html::tag('div', array(
            'class' => 'n2-ss-slide-background-mask'
        ), $videoHTML));
    }

    private function colorOnly($videoHTML, $fillMode, $backgroundColor, $backgroundImageOpacity, $backgroundImageBlur, $x, $y) {

        return N2Html::tag('div', array(
            "class"        => "n2-ss-slide-background n2-ow",
            "data-opacity" => $backgroundImageOpacity,
            "data-blur"    => $backgroundImageBlur,
            "data-mode"    => $fillMode,
            "data-x"       => $x,
            "data-y"       => $y
        ), N2Html::tag('div', array(
            'class' => 'n2-ss-slide-background-mask',
            "style" => $backgroundColor
        ), $videoHTML));
    }

    private function image($videoHTML, $fillMode, $backgroundColor, $backgroundImageOpacity, $backgroundImageBlur, $src, $imageData, $alt, $title, $x, $y) {
        $deviceAttributes = $this->getDeviceAttributes($src, $imageData);

        return N2Html::tag('div', $deviceAttributes + array(
                "class"        => "n2-ss-slide-background n2-ow",
                "data-opacity" => $backgroundImageOpacity,
                "data-blur"    => $backgroundImageBlur,
                "data-mode"    => $fillMode,
                "data-x"       => $x,
                "data-y"       => $y
            ), N2Html::tag('div', array(
            'class' => 'n2-ss-slide-background-mask',
            "style" => $backgroundColor
        ), N2Html::image($this->getDefaultImage($src, $deviceAttributes), $alt, array(
                "title" => $title
            )) . $videoHTML));
    }

    private function getDeviceAttributes($image, $imageData) {

        $attributes                 = array();
        $attributes['data-hash']    = md5($image);
        $attributes['data-desktop'] = N2ImageHelper::fixed($image);

        if ($imageData['desktop-retina']['image'] == '' && $imageData['tablet']['image'] == '' && $imageData['tablet-retina']['image'] == '' && $imageData['mobile']['image'] == '' && $imageData['mobile-retina']['image'] == '') {

        } else {
            if ($imageData['desktop-retina']['image'] != '') {
                $attributes['data-desktop-retina'] = N2ImageHelper::fixed($imageData['desktop-retina']['image']);
            }
            if ($imageData['tablet']['image'] != '') {
                $attributes['data-tablet'] = N2ImageHelper::fixed($imageData['tablet']['image']);
            }
            if ($imageData['tablet-retina']['image'] != '') {
                $attributes['data-tablet-retina'] = N2ImageHelper::fixed($imageData['tablet-retina']['image']);
            }
            if ($imageData['mobile']['image'] != '') {
                $attributes['data-mobile'] = N2ImageHelper::fixed($imageData['mobile']['image']);
            }
            if ($imageData['mobile-retina']['image'] != '') {
                $attributes['data-mobile-retina'] = N2ImageHelper::fixed($imageData['mobile-retina']['image']);
            }

            //We have to force the fade on load enabled to make sure the user get great result.
            $this->slider->features->fadeOnLoad->forceFadeOnLoad();
        }

        return $attributes;
    }

    private function getDefaultImage($src, $deviceAttributes) {
        if (count($deviceAttributes) > 2 || $this->slider->features->lazyLoad->isEnabled > 0) {
            return N2Image::base64Transparent();
        } else {
            return N2ImageHelper::fixed($src);
        }
    }

    private function makeBackgroundVideo($slide) {

        return '';
    }
}