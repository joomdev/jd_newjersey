<?php

N2Loader::import('libraries.slider.slides.slide.itemFactory', 'smartslider');

class N2SSItemButton extends N2SSItemAbstract {

    protected $type = 'button';

    public function render() {
        return $this->getHtml();
    }

    public function _renderAdmin() {
        return $this->getHtml();
    }

    private function getHtml() {
        $slide  = $this->layer->getSlide();
        $slider = $slide->getSlider();

        $this->loadResources($slider);

        $font = N2FontRenderer::render($this->data->get('font'), 'link', $slider->elementId, 'div#' . $slider->elementId . ' ', $slider->fontSize);

        $html = N2Html::openTag("div", array(
            "class" => "n2-ss-button-container n2-ow " . $font . ($this->data->get('fullwidth', 0) ? ' n2-ss-fullwidth' : '') . ($this->data->get('nowrap', 1) ? ' n2-ss-nowrap' : '')
        ));

        $content = '<span>' . $slide->fill($this->data->get("content")) . '</span>';

        $attrs = array();

        $style = N2StyleRenderer::render($this->data->get('style'), 'heading', $slider->elementId, 'div#' . $slider->elementId . ' ');
        $html .= $this->getLink('<span>' . $content . '</span>', $attrs + array(
                "class" => "{$style} n2-ow {$this->data->get('class', '')}"
            ), true);

        $html .= N2Html::closeTag("div");

        return $html;
    }

    public function loadResources($slider) {
        N2LESS::addFile(dirname(__FILE__) . "/button.n2less", $slider->cacheId, array(
            "sliderid" => $slider->elementId
        ), NEXTEND_SMARTSLIDER_ASSETS . '/less' . NDS);
    }
}