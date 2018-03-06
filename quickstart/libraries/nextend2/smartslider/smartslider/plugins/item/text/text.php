<?php

N2Loader::import('libraries.slider.slides.slide.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryText extends N2SSPluginItemFactoryAbstract {

    var $type = 'text';

    protected $priority = 2;

    private static $font = 1304;

    protected $layerProperties = array(
        "desktopportraitleft"   => 0,
        "desktopportraittop"    => 0,
        "desktopportraitwidth"  => 400,
        "desktopportraitalign"  => "left",
        "desktopportraitvalign" => "top"
    );

    protected $group = 'Basic';

    protected $class = 'N2SSItemText';

    public function __construct() {
        $this->_title = n2_x('Text', 'Slide item');
    }

    private static function initDefaultFont() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-text-font');
            if (is_array($res)) {
                self::$font = $res['value'];
            }
            if (is_numeric(self::$font)) {
                N2FontRenderer::preLoad(self::$font);
            }
            $inited = true;
        }
    }

    private static $style = '';

    private static function initDefaultStyle() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-text-style');
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
        self::initDefaultFont();
        $settings['font'][] = '<param name="item-text-font" type="font" previewmode="paragraph" label="' . n2_('Item') . ' - ' . n2_('Text') . '" default="' . self::$font . '" />';

        self::initDefaultStyle();
        $settings['style'][] = '<param name="item-text-style" type="style" set="heading" previewmode="heading" label="' . n2_('Item') . ' - ' . n2_('Text') . '" default="' . self::$style . '" />';
    }

    function getValues() {
        self::initDefaultFont();
        self::initDefaultStyle();

        return array(
            'content'       => 'Lorem ipsum dolor sit amet, <a href="#">consectetur adipiscing</a> elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
            'contenttablet' => '',
            'contentmobile' => '',
            'font'          => self::$font,
            'style'         => self::$style
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public static function getFilled($slide, $data) {
        $data->set('content', $slide->fill($data->get('content', '')));
        $data->set('contenttablet', $slide->fill($data->get('contenttablet', '')));
        $data->set('contentmobile', $slide->fill($data->get('contentmobile', '')));

        return $data;
    }

    public function prepareExport($export, $data) {
        $export->addVisual($data->get('font'));
        $export->addVisual($data->get('style'));
    }

    public function prepareImport($import, $data) {
        $data->set('font', $import->fixSection($data->get('font')));
        $data->set('style', $import->fixSection($data->get('style')));

        return $data;
    }
}

N2Plugin::addPlugin('ssitem', 'N2SSPluginItemFactoryText');

N2Pluggable::addAction('smartsliderDefault', 'N2SSPluginItemFactoryText::onSmartsliderDefaultSettings');
