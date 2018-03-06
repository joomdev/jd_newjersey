<?php

N2Loader::import('libraries.slider.slides.slide.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryImage extends N2SSPluginItemFactoryAbstract {

    var $type = 'image';

    protected $priority = 4;

    protected $layerProperties = array("desktopportraitwidth" => "300");

    private static $style = '';

    protected $group = 'Basic';

    protected $class = 'N2SSItemImage';

    public function __construct() {
        $this->_title = n2_x('Image', 'Slide item');
    }

    private static function initDefaultStyle() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-image-style');
            if (is_array($res)) {
                self::$style = $res['value'];
            }
            if (is_numeric(self::$style)) {
                N2StyleRenderer::preLoad(self::$style);
            }
            $inited = true;
        }
    }

    public static function onSmartsliderDefaultSettings(&$settings) {
        self::initDefaultStyle();
        $settings['style'][] = '<param name="item-image-style" type="style" previewmode="box" label="Item - Image" default="' . self::$style . '" />';
    }

    function getValues() {
        self::initDefaultStyle();

        return array(
            'image'          => '$system$/images/placeholder/image.png',
            'alt'            => n2_('Image is not available'),
            'title'          => '',
            'link'           => '#|*|_self',
            'size'           => 'auto|*|auto',
            'style'          => self::$style,
            'cssclass'       => '',
            'image-optimize' => 1
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public static function getFilled($slide, $data) {
        $data->set('image', $slide->fill($data->get('image', '')));
        $data->set('alt', $slide->fill($data->get('alt', '')));
        $data->set('title', $slide->fill($data->get('title', '')));
        $data->set('link', $slide->fill($data->get('link', '#|*|')));

        return $data;
    }

    public function prepareExport($export, $data) {
        $export->addImage($data->get('image'));
        $export->addVisual($data->get('style'));
        $export->addLightbox($data->get('link'));
    }

    public function prepareImport($import, $data) {
        $data->set('image', $import->fixImage($data->get('image')));
        $data->set('style', $import->fixSection($data->get('style')));
        $data->set('link', $import->fixLightbox($data->get('link')));

        return $data;
    }

    public function prepareSample($data) {
        $data->set('image', N2ImageHelper::fixed($data->get('image')));

        return $data;
    }

}

N2Plugin::addPlugin('ssitem', 'N2SSPluginItemFactoryImage');

N2Pluggable::addAction('smartsliderDefault', 'N2SSPluginItemFactoryImage::onSmartsliderDefaultSettings');