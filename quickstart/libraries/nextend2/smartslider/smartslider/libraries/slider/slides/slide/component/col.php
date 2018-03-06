<?php

class N2SSSlideComponentCol extends N2SSSlideComponent {

    protected $type = 'col';

    protected $colAttributes = array(
        'class' => 'n2-ss-layer-col n2-ss-layer-content',
        'style' => ''
    );

    public function __construct($index, $slide, $group, $data, $placenentType) {
        parent::__construct($index, $slide, $group, $data, $placenentType);

        $this->container = new N2SSSlideContainer($slide, $this, $data['layers'], 'normal');
        $this->data->un_set('layers');

        $this->attributes['style'] = '';

        $this->colAttributes['data-verticalalign'] = $this->data->get('verticalalign');

        $innerAlign = $this->data->get('desktopportraitinneralign');
        if (!empty($innerAlign)) {
            $this->attributes['data-csstextalign'] = $innerAlign;
        }

        $this->colAttributes['style'] .= 'padding:' . $this->spacingToEm($this->data->get('desktopportraitpadding')) . ';';

        $this->colAttributes['style'] .= $this->renderBackground();

        $borderRadius = intval($this->data->get('borderradius'));
        if ($borderRadius > 0) {
            $this->colAttributes['style'] .= 'border-radius:' . $borderRadius . 'px;';
        }

        $boxShadow = explode('|*|', $this->data->get('boxshadow'));
        if (count($boxShadow) == 5 && ($boxShadow[0] != 0 || $boxShadow[1] != 0 || $boxShadow[2] != 0 || $boxShadow[3] != 0) && N2Color::hex2alpha($boxShadow[4]) != 0) {
            $this->colAttributes['style'] .= 'box-shadow:' . $boxShadow[0] . 'px ' . $boxShadow[1] . 'px ' . $boxShadow[2] . 'px ' . $boxShadow[3] . 'px ' . N2Color::colorToRGBA($boxShadow[4]) . ';';
        }

        $borderStyle = $this->data->get('borderstyle', 'none');
        if ($borderStyle != 'none') {

            $this->colAttributes['style'] .= 'border-color:' . N2Color::colorToRGBA($this->data->get('bordercolor', 'ffffffff')) . ';';
            $this->colAttributes['style'] .= 'border-style:' . $borderStyle . ';';

            $values    = explode('|*|', $this->data->get('borderwidth', '1|*|1|*|1|*|1'));
            $unit      = 'px';
            $values[4] = '';
            $this->colAttributes['style'] .= 'border-width:' . implode($unit . ' ', $values) . ';';
        }

        $maxWidth = intval($this->data->get('desktopportraitmaxwidth'));
        if ($maxWidth > 0) {
            $this->attributes['style'] .= 'max-width: ' . $maxWidth . 'px;';
            $this->attributes['class'] .= ' n2-ss-has-maxwidth ';
        }
        $this->createDeviceProperty('maxwidth');

        $this->placement->attributes($this->attributes);

        $this->createDeviceProperty('padding');
        $this->createDeviceProperty('inneralign');

        $this->createDeviceProperty('order');

        $width = explode('/', $this->data->get('colwidth', 1));
        if (count($width) == 2) {
            if ($width[0] == 0 || $width[1] == 0) {
                $width[0] = 1;
                $width[1] = 2;
                $this->data->set('colwidth', '1/2');
            }
            $width = round($width[0] / $width[1] * 100, 1);
        } else {
            $width = 100;
        }

        $this->attributes['style'] .= 'width: ' . $width . '%;';

        if (!N2SSSlideComponent::$isAdmin) {
            $this->makeLink();
        }
    }

    public function updateRowSpecificProperties($gutter) {

        $this->attributes['style'] .= 'margin-right: ' . $gutter . 'px;margin-left: ' . $gutter . 'px;margin-top: ' . $gutter . 'px;';

    }

    private function makeLink() {

        N2Loader::import('libraries.link.link');

        list($link, $target) = array_pad((array)N2Parse::parse($this->data->get('link', '#|*|')), 2, '');

        if (($link != '#' && !empty($link))) {

            $link                          = N2LinkParser::parse($this->slide->fill($link), $this->attributes);
            $this->attributes['data-href'] = $link;

            if (!isset($this->attributes['onclick'])) {
                if (empty($target) || $target == '_self') {
                    $this->attributes['onclick'] = 'window.location=this.getAttribute("data-href");';
                } else {
                    $this->attributes['onclick'] = 'var w=window.open();w.opener=null;w.location=this.getAttribute("data-href");';
                }
            }
            $this->attributes['style'] .= 'cursor:pointer;';

        }
    }

    public function render() {
        if ($this->isRenderAllowed()) {
            $this->prepareHTML();
            $html = N2Html::tag('div', $this->colAttributes, parent::renderContainer());
            $html = $this->renderPlugins($html);

            return N2Html::tag('div', $this->attributes, $html);
        }

        return '';
    }

    public function admin() {

        $this->createProperty('colwidth');
        $this->createProperty('link');

        $this->createProperty('verticalalign');

        $this->createProperty('bgimage');
        $this->createProperty('bgimagex');
        $this->createProperty('bgimagey');
        $this->createProperty('bgimageparallax');
        $this->createProperty('bgcolor');
        $this->createProperty('bgcolorgradient');
        $this->createProperty('bgcolorgradientend');

        $this->createProperty('borderradius');
        $this->createProperty('boxshadow');

        $this->createProperty('borderwidth');
        $this->createProperty('borderstyle');
        $this->createProperty('bordercolor');

        $this->createProperty('opened', 1);

        parent::admin();
    }


    /**
     * @param N2SmartSliderExport $export
     * @param array               $layer
     */
    public static function prepareExport($export, $layer) {
        if (!empty($layer['bgimage'])) {
            $export->addImage($layer['bgimage']);
        }

        N2SmartSliderExport::prepareExportLayer($export, $layer['layers']);
    }

    public static function prepareImport($import, &$layer) {
        if (!empty($layer['bgimage'])) {
            $layer['bgimage'] = $import->fixImage($layer['bgimage']);
        }

        N2SmartSliderImport::prepareImportLayer($import, $layer['layers']);
    }

    public static function prepareSample(&$layer) {
        if (!empty($layer['bgimage'])) {
            $layer['bgimage'] = N2ImageHelper::fixed($layer['bgimage']);
        }

        N2SmartsliderSlidesModel::prepareSample($layer['layers']);
    }

    /**
     * @param N2SmartSliderSlide $slide
     * @param array              $layer
     */
    public static function getFilled($slide, &$layer) {

        $slide->fillLayers($layer['layers']);
    }
}