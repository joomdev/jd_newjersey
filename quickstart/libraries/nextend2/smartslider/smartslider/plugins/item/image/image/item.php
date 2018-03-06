<?php

N2Loader::import('libraries.slider.slides.slide.itemFactory', 'smartslider');

class N2SSItemImage extends N2SSItemAbstract {

    protected $type = 'image';

    public function render() {
        return $this->getHtml();
    }

    public function _renderAdmin() {
        return $this->getHtml();
    }

    private function getHtml() {
        $slide  = $this->layer->getSlide();
        $slider = $slide->getSlider();


        $size = (array)N2Parse::parse($this->data->get('size', ''));
        if (empty($size[0])) $size[0] = 'auto';
        if (empty($size[1])) $size[1] = 'auto';


        $html = N2Html::tag('img', $this->optimizeImage($slide->fill($this->data->get('image', ''))) + array(
                "id"    => $this->id,
                "alt"   => htmlspecialchars($slide->fill($this->data->get('alt', ''))),
                "style" => "display: inline-block; max-width: 100%; width: {$size[0]};height: {$size[1]};",
                "class" => $this->data->get('cssclass', '') . ' n2-ow',
                "title" => htmlspecialchars($slide->fill($this->data->get('title', '')))
            ), false);

        $style = N2StyleRenderer::render($this->data->get('style'), 'heading', $slider->elementId, 'div#' . $slider->elementId . ' ');

        return N2Html::tag("div", array(
            "class" => $style . ' n2-ss-img-wrapper n2-ow',
            'style' => 'overflow:hidden;'
        ), $this->getLink($html, array('class' => 'n2-ow')));
    }
}