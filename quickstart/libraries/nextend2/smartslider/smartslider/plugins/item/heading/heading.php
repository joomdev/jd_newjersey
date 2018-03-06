<?php

N2Loader::import('libraries.slider.slides.slide.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryHeading extends N2SSPluginItemFactoryAbstract {

    var $type = 'heading';

    protected $priority = 1;

    private static $font = 1009;
    private static $style = '';

    protected $group = 'Basic';

    protected $class = 'N2SSItemHeading';

    public function __construct() {
        $this->_title = n2_x('Heading', 'Slide item');
    }

    function getValues() {
        self::initDefault();

        return array(
            'priority'  => 'div',
            'fullwidth' => 1,
            'nowrap'    => 0,
            'heading'   => n2_('Heading layer'),
            'title'     => '',
            'link'      => '#|*|_self',
            'font'      => self::$font,
            'style'     => self::$style,

            'split-text-transform-origin'    => '50|*|50|*|0',
            'split-text-backface-visibility' => 1,

            'split-text-animation-in' => '',
            'split-text-delay-in'     => 0,

            'split-text-animation-out' => '',
            'split-text-delay-out'     => 0,

            'class' => ''
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public static function getFilled($slide, $data) {
        $data->set('heading', $slide->fill($data->get('heading', '')));
        $data->set('link', $slide->fill($data->get('link', '#|*|')));

        return $data;
    }

    public function prepareExport($export, $data) {
        $export->addVisual($data->get('font'));
        $export->addVisual($data->get('style'));
        $export->addLightbox($data->get('link'));
    }

    public function prepareImport($import, $data) {
        $data->set('font', $import->fixSection($data->get('font')));
        $data->set('style', $import->fixSection($data->get('style')));
        $data->set('link', $import->fixLightbox($data->get('link')));

        return $data;
    }

    private static function initDefault() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-heading-font');
            if (is_array($res)) {
                self::$font = $res['value'];
            }
            if (is_numeric(self::$font)) {
                N2FontRenderer::preLoad(self::$font);
            }

            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-heading-style');
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
        self::initDefault();
        $settings['font'][] = '<param name="item-heading-font" type="font" previewmode="hover" label="' . n2_('Item') . ' - ' . n2_('Heading') . '" default="' . self::$font . '" />';

        $settings['style'][] = '<param name="item-heading-style" type="style" set="heading" previewmode="heading" label="' . n2_('Item') . ' - ' . n2_('Heading') . '" default="' . self::$style . '" />';
    }

}

N2Plugin::addPlugin('ssitem', 'N2SSPluginItemFactoryHeading');

N2Pluggable::addAction('smartsliderDefault', 'N2SSPluginItemFactoryHeading::onSmartsliderDefaultSettings');

