<?php

abstract class  N2SSSlideComponent {

    public static $isAdmin = false;

    /**
     * @var N2SmartSliderSlide
     */
    protected $slide;

    protected $type = '';
    /**
     * @var N2SSSlideComponent
     */
    protected $group;

    /**
     * @var N2SSSlidePlacement
     */
    protected $placement;

    /**
     * @var N2SSSlideContainer
     */
    protected $container = false;

    protected $fontSizeModifier = 100;

    protected $baseSize = 16;

    protected $attributes = array(
        'class' => 'n2-ss-layer n2-ow',
        'style' => ''
    );

    public $data;

    /**
     * N2SSSlideComponent constructor.
     *
     * @param int                 $index
     * @param N2SmartSliderSlide  $slide
     * @param N2SSSlideComponent  $group
     * @param                     $data
     * @param string              $placenentType
     */
    public function __construct($index, $slide, $group, $data, $placenentType = 'absolute') {
        $this->slide = $slide;
        $this->group = $group;

        $this->data = new N2Data($data);

        $this->fontSizeModifier = $this->data->get('desktopportraitfontsize', 100);
        if (!is_numeric($this->fontSizeModifier)) {
            $this->fontSizeModifier = 100;
        }

        $this->baseSize = $group->getBaseSize();

        switch ($placenentType) {
            case 'content':
                $this->placement = new N2SSSlidePlacementContent($this, $index);
                break;
            case 'normal':
                $this->placement = new N2SSSlidePlacementNormal($this, $index);
                break;
            case 'default':
                $this->placement = new N2SSSlidePlacementDefault($this, $index);
                break;
            case 'group':
                $this->placement = new N2SSSlidePlacementGroup($this, $index);
                break;
            case 'absolute':
            default:
                $this->placement = new N2SSSlidePlacementAbsolute($this, $index);
                break;
        }
    }

    /**
     * @return N2SmartSliderSlide
     */
    public function getSlide() {
        return $this->slide;
    }

    /**
     * @return int
     */
    public function getBaseSize() {
        return $this->baseSize * $this->fontSizeModifier / 100;
    }

    public function isRenderAllowed() {
        $generatorVisible = $this->data->get('generatorvisible');
        if (!empty($generatorVisible) && $this->slide->hasGenerator() && !self::$isAdmin) {
            $filled = $this->slide->fill($generatorVisible);
            if (empty($filled)) {
                return false;
            }
        }

        return true;
    }

    public abstract function render();

    protected function renderContainer() {

        if ($this->container) {
            return $this->container->render();
        }

        return '';
    }

    public function admin() {

        $this->createProperty('id');
        $this->createProperty('class');
        $this->createProperty('name');
        $this->createProperty('namesynced');
        $this->createProperty('status');
        $this->createProperty('generatorvisible');
        if ($this->container) {
            $this->container->admin();
        }
        $this->placement->adminAttributes($this->attributes);
    }

    public function pxToEm($value) {
        $unit     = 'px';
        $baseSize = $this->getBaseSize();
        if ($baseSize > 0) {
            $unit  = 'em';
            $value = intval($value) / $baseSize;
        }

        return $value . $unit;
    }

    public function spacingToEm($value) {
        $values   = explode('|*|', $value);
        $unit     = $values[4];
        $baseSize = $this->getBaseSize();
        if ($unit == 'px+' && $baseSize > 0) {
            $unit = 'em';
            for ($i = 0; $i < 4; $i++) {
                $values[$i] = intval($values[$i]) / $baseSize;
            }
        }
        $values[4] = '';

        return implode($unit . ' ', $values);
    }

    protected function prepareHTML() {
        $this->attributes['data-type'] = $this->type;

        $id = $this->data->get('id');
        if (!empty($id)) {
            $this->attributes['id'] = $id;
        }

        $class = $this->data->get('class');
        if (!empty($class)) {
            $this->attributes['class'] .= ' ' . $class;
        }

    }

    protected function renderPlugins($html) {
        $this->pluginRotation();
        $html = $this->pluginCrop($html);
        $this->pluginAnimations();
        $this->pluginShowOn();
        $this->pluginFontSize();
        $this->pluginParallax();
        $this->attributes['data-plugin'] = 'rendered';

        return $html;
    }

    private function pluginRotation() {

        $this->createProperty('rotation', 0);
    }

    private function pluginCrop($html) {

        $cropStyle = $this->data->get('crop', 'visible');

        if (self::$isAdmin) {
            if ($cropStyle == 'auto') {
                $cropStyle = 'hidden';
            }
        } else {
            if ($cropStyle == 'auto') {
                $this->attributes['class'] .= ' n2-scrollable';
            }
        }

        if ($cropStyle == 'mask') {
            $cropStyle = 'hidden';
            $html      = N2Html::tag('div', array('class' => 'n2-ss-layer-mask'), $html);

            $this->attributes['data-animatableselector'] = '.n2-ss-layer-mask:first';
        } else if (!self::$isAdmin && $this->data->get('parallax') > 0) {
            $html = N2Html::tag('div', array(
                'class' => 'n2-ss-layer-parallax'
            ), $html);

            $this->attributes['data-animatableselector'] = '.n2-ss-layer-parallax:first';
        }

        $this->attributes['style'] .= 'overflow:' . $cropStyle . ';';

        if (self::$isAdmin) {
            $crop = $this->data->get('crop', 'visible');
            if (empty($crop)) $crop = 'visible';
            $this->attributes['data-crop'] = $crop;
        }

        return $html;
    }


    private function pluginAnimations() {
        $animations = $this->data->get('animations');
        if (!empty($animations)) {
            //Fix empty assoc arrays as they json_encoded into [] instead of {}
            if (isset($animations['in']) && is_array($animations['in'])) {
                for ($i = 0; $i < count($animations['in']); $i++) {
                    $animations['in'][$i] = (object)$animations['in'][$i];
                }
            }
            if (isset($animations['loop']) && is_array($animations['loop'])) {
                for ($i = 0; $i < count($animations['loop']); $i++) {
                    $animations['loop'][$i] = (object)$animations['loop'][$i];
                }
            }
            if (isset($animations['out']) && is_array($animations['out'])) {
                for ($i = 0; $i < count($animations['out']); $i++) {
                    $animations['out'][$i] = (object)$animations['out'][$i];
                }
            }
            $this->attributes['data-animations'] = n2_base64_encode(json_encode($animations));
        }

        $this->pluginAnimationGetEventAttributes();
    }


    private function pluginAnimationGetEventAttributes() {

        if (!self::$isAdmin) {
            $sliderId = $this->slide->getSlider()->sliderId;

            $click = $this->data->get('click');
            if (!empty($click)) {
                $this->attributes['data-click'] = $this->pluginAnimationParseEventCode($click, $sliderId);
                $this->attributes['style']      .= 'cursor:pointer;';
            }
            $mouseenter = $this->data->get('mouseenter');
            if (!empty($mouseenter)) {
                $this->attributes['data-mouseenter'] = $this->pluginAnimationParseEventCode($mouseenter, $sliderId);
            }
            $mouseleave = $this->data->get('mouseleave');
            if (!empty($mouseleave)) {
                $this->attributes['data-mouseleave'] = $this->pluginAnimationParseEventCode($mouseleave, $sliderId);
            }
            $play = $this->data->get('play');
            if (!empty($play)) {
                $this->attributes['data-play'] = $this->pluginAnimationParseEventCode($play, $sliderId);
            }
            $pause = $this->data->get('pause');
            if (!empty($pause)) {
                $this->attributes['data-pause'] = $this->pluginAnimationParseEventCode($pause, $sliderId);
            }
            $stop = $this->data->get('stop');
            if (!empty($stop)) {
                $this->attributes['data-stop'] = $this->pluginAnimationParseEventCode($stop, $sliderId);
            }
        } else {

            $click = $this->data->get('click');
            if (!empty($click)) {
                $this->attributes['data-click'] = $click;
            }
            $mouseenter = $this->data->get('mouseenter');
            if (!empty($mouseenter)) {
                $this->attributes['data-mouseenter'] = $mouseenter;
            }
            $mouseleave = $this->data->get('mouseleave');
            if (!empty($mouseleave)) {
                $this->attributes['data-mouseleave'] = $mouseleave;
            }
            $play = $this->data->get('play');
            if (!empty($play)) {
                $this->attributes['data-play'] = $play;
            }
            $pause = $this->data->get('pause');
            if (!empty($pause)) {
                $this->attributes['data-pause'] = $pause;
            }
            $stop = $this->data->get('stop');
            if (!empty($stop)) {
                $this->attributes['data-stop'] = $stop;
            }
        }
    }

    private function pluginAnimationParseEventCode($code, $elementId) {
        if (preg_match('/^[a-zA-Z0-9_\-,]+$/', $code)) {
            if (is_numeric($code)) {
                $code = "window['" . $elementId . "'].changeTo(" . ($code - 1) . ");";
            } else if ($code == 'next') {
                $code = "window['" . $elementId . "'].next();";
            } else if ($code == 'previous') {
                $code = "window['" . $elementId . "'].previous();";
            } else {
                $code = "n2ss.trigger(this, '" . $code . "');";
            }
        }

        return $code;
    }


    private function pluginShowOn() {
        $this->createDeviceProperty('', 1);
    }

    private function pluginFontSize() {
        $this->attributes['data-adaptivefont'] = $this->data->get('adaptivefont');

        $this->createDeviceProperty('fontsize');
    }

    public function pluginParallax() {

        $parallax = intval($this->data->get('parallax'));
        if (self::$isAdmin || $parallax >= 1) {
            $this->attributes['data-parallax'] = $parallax;
        }
    }

    public function createProperty($name, $default = null) {
        $this->attributes['data-' . $name] = $this->data->get($name, $default);
    }

    public function createDeviceProperty($name, $default = null) {
        $devices = array(
            'desktopportrait',
            'desktoplandscape',
            'tabletportrait',
            'tabletlandscape',
            'mobileportrait',
            'mobilelandscape'
        );
        foreach ($devices AS $device) {
            $this->attributes['data-' . $device . $name] = $this->data->get($device . $name, $default);
        }
    }

    protected function renderBackground() {
        $background = '';
        $image      = $this->slide->fill($this->data->get('bgimage'));
        if ($image != '') {
            $x          = intval($this->data->get('bgimagex', 50));
            $y          = intval($this->data->get('bgimagey', 50));
            $background .= 'URL("' . N2ImageHelper::fixed($image) . '") ' . $x . '% ' . $y . '% / cover no-repeat' . ($this->data->get('bgimageparallax', 0) ? ' fixed' : '');
        }

        $color    = $this->data->get('bgcolor');
        $gradient = $this->data->get('bgcolorgradient', 'off');
        $colorend = $this->data->get('bgcolorgradientend');

        if (N2Color::hex2alpha($color) != 0 || ($gradient != 'off' && N2Color::hex2alpha($colorend) != 0)) {
            $after = '';
            if ($background != '') {
                $after .= ',' . $background;
            }
            switch ($gradient) {
                case 'horizontal':
                    return 'background:-moz-linear-gradient(left, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorend) . ' 100%)' . $after . ';' . 'background:-webkit-linear-gradient(left, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorend) . ' 100%)' . $after . ';' . 'background:linear-gradient(to right, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorend) . ' 100%)' . $after . ';';
                    break;
                case 'vertical':
                    return 'background:-moz-linear-gradient(top, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorend) . ' 100%)' . $after . ';' . 'background:-webkit-linear-gradient(top, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorend) . ' 100%)' . $after . ';' . 'background:linear-gradient(to bottom, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorend) . ' 100%)' . $after . ';';
                    break;
                case 'diagonal1':
                    return 'background:-moz-linear-gradient(45deg, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorend) . ' 100%)' . $after . ';' . 'background:-webkit-linear-gradient(45deg, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorend) . ' 100%)' . $after . ';' . 'background:linear-gradient(45deg, ' . N2Color::colorToRGBA($color) . ' 0%,' . N2Color::colorToRGBA($colorend) . ' 100%)' . $after . ';';
                    break;
                case 'diagonal2':
                    return 'background:-moz-linear-gradient(-45deg, ' . N2Color::colorToRGBA($colorend) . ' 0%,' . N2Color::colorToRGBA($color) . ' 100%)' . $after . ';' . 'background:-webkit-linear-gradient(-45deg, ' . N2Color::colorToRGBA($colorend) . ' 0%,' . N2Color::colorToRGBA($color) . ' 100%)' . $after . ';' . 'background:linear-gradient(-45deg, ' . N2Color::colorToRGBA($colorend) . ' 0%,' . N2Color::colorToRGBA($color) . ' 100%)' . $after . ';';
                    break;
                case 'off':
                default:
                    if ($background != '') {
                        return "background:linear-gradient(" . N2Color::colorToRGBA($color) . ", " . N2Color::colorToRGBA($color) . ")" . $after . ';';
                    } else {
                        return "background:" . N2Color::colorToRGBA($color) . ';';
                    }

                    break;
            }
        } else if (($background != '')) {
            return "background:" . $background . ';';
        }

        return '';
    }

    /**
     * @param N2SmartSliderSlide $slide
     * @param array              $layer
     */
    public static function getFilled($slide, &$layer) {

    }

    /**
     * @param N2SmartSliderExport $export
     * @param array               $layer
     */
    public static function prepareExport($export, $layer) {

    }

    /**
     * @param N2SmartSliderImport $import
     * @param array               $layer
     */
    public static function prepareImport($import, &$layer) {

    }

    /**
     * @param array $layer
     */
    public static function prepareSample(&$layer) {

    }

    public function getAttribute($key) {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return null;
    }

    public function setAttribute($key, $value) {
        $this->attributes[$key] = $value;
    }

}

N2Loader::importAll("libraries.slider.slides.slide.component", "smartslider");