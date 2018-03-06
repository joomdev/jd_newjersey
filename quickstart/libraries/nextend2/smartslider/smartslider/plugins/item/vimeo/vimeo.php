<?php

N2Loader::import('libraries.slider.slides.slide.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryVimeo extends N2SSPluginItemFactoryAbstract {

    var $type = 'vimeo';

    protected $priority = 20;

    protected $layerProperties = array(
        "desktopportraitwidth"  => 300,
        "desktopportraitheight" => 180
    );

    protected $group = 'Media';

    protected $class = 'N2SSItemVimeo';

    public function __construct() {
        $this->_title = n2_x('Vimeo', 'Slide item');
    }

    function getValues() {
        return array(
            'vimeourl' => '75251217',
            'image'    => '$system$/images/placeholder/video.png',
            'center'   => 0,
            'autoplay' => 0,
            'title'    => 1,
            'byline'   => 1,
            'portrait' => 0,
            'color'    => '00adef',
            'loop'     => 0,
            'start'    => 0
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public static function getFilled($slide, $data) {
        $data->set('image', $slide->fill($data->get('image', '')));
        $data->set('vimeourl', $slide->fill($data->get('vimeourl', '')));

        return $data;
    }

    public function prepareExport($export, $data) {
        $export->addImage($data->get('image'));
    }

    public function prepareImport($import, $data) {
        $data->set('image', $import->fixImage($data->get('image')));

        return $data;
    }

    public function prepareSample($data) {
        $data->set('image', N2ImageHelper::fixed($data->get('image')));

        return $data;
    }

}

N2Plugin::addPlugin('ssitem', 'N2SSPluginItemFactoryVimeo');