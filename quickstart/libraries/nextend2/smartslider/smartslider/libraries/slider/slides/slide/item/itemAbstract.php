<?php

abstract class N2SSItemAbstract {

    protected $id;

    /** @var N2SSSlideComponentLayer */
    protected $layer;

    /** @var N2Data */
    protected $data;

    protected $type = '';

    protected $isEditor = false;

    /**
     * N2SSItemAbstract constructor.
     *
     * @param string                  $id
     * @param array                   $itemData
     * @param N2SSSlideComponentLayer $layer
     */
    public function __construct($id, $itemData, $layer) {
        $this->id    = $id;
        $this->data  = new N2Data($itemData);
        $this->layer = $layer;
    }

    public abstract function render();

    public function renderAdmin() {
        $this->isEditor = true;

        $json = $this->data->toJson();

        return N2Html::tag("div", array(
            "class"           => "n2-ss-item n2-ss-item-" . $this->type,
            "data-item"       => $this->type,
            "data-itemvalues" => $json
        ), $this->_renderAdmin());
    }

    protected abstract function _renderAdmin();

    public function needSize() {
        return false;
    }

    protected function getLink($content, $attributes = array(), $renderEmpty = false) {

        N2Loader::import('libraries.link.link');

        list($link, $target, $rel) = array_pad((array)N2Parse::parse($this->data->get('link', '#|*||*|')), 3, '');

        if (($link != '#' && !empty($link)) || $renderEmpty === true) {

            $link = N2LinkParser::parse($this->layer->getSlide()
                                                    ->fill($link), $attributes, $this->isEditor);
            if (!empty($target) && $target != '_self') {
                $attributes['target'] = $target;
            }
            if (!empty($rel)) {
                $attributes['rel'] = $rel;
            }

            return N2Html::link($content, $link, $attributes);
        }

        return $content;
    }

    protected function optimizeImage($image) {
        $slider   = $this->layer->getSlide()
                                ->getSlider();
        $lazyLoad = $slider->features->lazyLoad;

        $imagePath = N2ImageHelper::fixed($image, true);
        if (isset($imagePath[0]) && $imagePath[0] == '/' && $imagePath[1] != '/' && $lazyLoad->layerImageSizeBase64 && $lazyLoad->layerImageSizeBase64Size && filesize($imagePath) < $lazyLoad->layerImageSizeBase64Size) {
            return array(
                'src' => N2Image::base64($imagePath, $image)
            );
        }
        if (!$lazyLoad->layerImageOptimize || !$this->data->get('image-optimize', 1)) {
            return array(
                'src' => N2ImageHelper::fixed($image)
            );
        }

        $quality = intval($slider->params->get('optimize-quality', 70));

        $tablet = N2Image::scaleImage('image', $image, $lazyLoad->layerImageTablet, false, $quality);
        $mobile = N2Image::scaleImage('image', $image, $lazyLoad->layerImageMobile, false, $quality);

        if ($image == $tablet && $image == $mobile) {
            return array(
                'src' => N2ImageHelper::fixed($image)
            );
        }

        return array(
            'src'          => N2Image::base64Transparent(),
            'data-desktop' => N2ImageHelper::fixed($image),
            'data-tablet'  => N2ImageHelper::fixed($tablet),
            'data-mobile'  => N2ImageHelper::fixed($mobile),
            'data-device'  => '1'
        );
    }
}