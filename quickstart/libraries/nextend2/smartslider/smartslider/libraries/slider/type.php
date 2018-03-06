<?php

abstract class N2SmartSliderType {

    /**
     * @var N2SmartSliderAbstract
     */
    protected $slider;

    protected $javaScriptProperties;

    /** @var  N2SmartSliderWidgets */
    protected $widgets;

    protected $shapeDividerAdded = false;

    public function __construct($slider) {
        $this->slider     = $slider;
        $slider->fontSize = intval($slider->params->get('fontsize', '16'));
    }

    public static function getItemDefaults() {
        return array();
    }

    public function render() {

        $this->javaScriptProperties = $this->slider->features->generateJSProperties();

        $this->widgets = new N2SmartSliderWidgets($this->slider);

        ob_start();
        $this->renderType();

        return ob_get_clean();
    }

    protected abstract function renderType();

    protected function getSliderClasses() {
        return $this->slider->features->fadeOnLoad->getSliderClass();
    }

    protected function openSliderElement() {
        return N2Html::openTag('div', array(
                'id'           => $this->slider->elementId,
                'data-creator' => 'Smart Slider 3',
                'class'        => 'n2-ss-slider n2-ow n2-has-hover n2notransition ' . $this->getSliderClasses(),

            ) + $this->getFontSizeAttributes());
    }

    private function getFontSizeAttributes() {

        return $this->slider->features->responsive->getMinimumFontSizeAttributes() + array(
                'style'         => "font-size: " . $this->slider->fontSize . "px;",
                'data-fontsize' => $this->slider->fontSize
            );
    }

    public function getDefaults() {
        return array();
    }

    /**
     * @param $params N2Data
     */
    public function limitParams($params) {

    }

    protected function initParticleJS() {
    }

    protected function renderShapeDividers() {
    }

    private function renderShapeDivider($side, $params) {
    }
}