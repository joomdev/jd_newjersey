<?php

N2Loader::import('libraries.slider.slides.slide.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryYouTube extends N2SSPluginItemFactoryAbstract {

    var $type = 'youtube';

    protected $priority = 20;

    protected $layerProperties = array(
        "desktopportraitwidth"  => 300,
        "desktopportraitheight" => 180
    );

    protected $group = 'Media';

    protected $class = 'N2SSItemYouTube';

    public function __construct() {
        $this->_title = n2_x('YouTube', 'Slide item');
    }

    function getValues() {
        return array(
            'code'           => 'qesNtYIBDfs',
            'youtubeurl'     => 'https://www.youtube.com/watch?v=lsq09izc1H4',
            'image'          => '$system$/images/placeholder/video.png',
            'autoplay'       => 0,
            'controls'       => 1,
            'defaultimage'   => 'maxresdefault',
            'related'        => '0',
            'vq'             => 'default',
            'center'         => 0,
            'loop'           => 0,
            'showinfo'       => 1,
            'modestbranding' => 1,
            'reset'          => 0,
            'start'          => '0',
            'playbutton'     => 1
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public static function getFilled($slide, $data) {
        $data->set('image', $slide->fill($data->get('image', '')));
        $data->set('youtubeurl', $slide->fill($data->get('youtubeurl', '')));

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

N2Plugin::addPlugin('ssitem', 'N2SSPluginItemFactoryYouTube');