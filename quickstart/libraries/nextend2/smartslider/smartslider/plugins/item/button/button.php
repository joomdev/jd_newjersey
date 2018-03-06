<?php

N2Loader::import('libraries.slider.slides.slide.item.itemFactoryAbstract', 'smartslider');

class N2SSPluginItemFactoryButton extends N2SSPluginItemFactoryAbstract {

    public $type = 'button';

    protected $priority = 3;

    private static $font = 1103;

    protected $group = 'Basic';

    protected $class = 'N2SSItemButton';

    public function __construct() {
        $this->_title = n2_x('Button', 'Slide item');
    }

    private static function initDefaultFont() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-button-font');
            if (is_array($res)) {
                self::$font = $res['value'];
            }
            if (is_numeric(self::$font)) {
                N2FontRenderer::preLoad(self::$font);
            }
            $inited = true;
        }
    }

    private static $style = 1101;

    private static function initDefaultStyle() {
        static $inited = false;
        if (!$inited) {
            $res = N2StorageSectionAdmin::get('smartslider', 'default', 'item-button-style');
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
        $settings['font'][] = '<param name="item-button-font" type="font" previewmode="link" set="1100" label="' . n2_('Item') . ' - ' . n2_('Button') . '" default="' . self::$font . '" />';

        self::initDefaultStyle();
        $settings['style'][] = '<param name="item-button-style" type="style" previewmode="button" set="1100" label="' . n2_('Item') . ' - ' . n2_('Button') . '" default="' . self::$style . '" />';
    }

    function getValues() {
        self::initDefaultFont();
        self::initDefaultStyle();

        return array(
            'content'       => n2_('MORE'),
            'nowrap'        => 1,
            'fullwidth'     => 0,
            'link'          => '#|*|_self',
            'font'          => self::$font,
            'style'         => self::$style,
            'class'         => '',
            'icon'          => '',
            'iconsize'      => '100',
            'iconspacing'   => '30',
            'iconplacement' => 'left',
        );
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->type . DIRECTORY_SEPARATOR;
    }

    public static function getFilled($slide, $data) {
        $data->set('content', $slide->fill($data->get('content', '')));
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

    public function loadResources($slider) {
        parent::loadResources($slider);

        N2LESS::addFile($this->getPath() . "/button.n2less", $slider->cacheId, array(
            "sliderid" => $slider->elementId
        ), NEXTEND_SMARTSLIDER_ASSETS . '/less' . NDS);
    }
}

N2Plugin::addPlugin('ssitem', 'N2SSPluginItemFactoryButton');

N2Pluggable::addAction('smartsliderDefault', 'N2SSPluginItemFactoryButton::onSmartsliderDefaultSettings');
