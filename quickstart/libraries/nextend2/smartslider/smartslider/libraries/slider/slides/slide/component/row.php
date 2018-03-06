<?php

class N2SSSlideComponentRow extends N2SSSlideComponent {

    protected $type = 'row';

    protected $rowAttributes = array(
        'class' => 'n2-ss-layer-row ',
        'style' => ''
    );

    protected $html = '';

    public function __construct($index, $slide, $group, $data, $placenentType) {
        parent::__construct($index, $slide, $group, $data, $placenentType);
        $this->container = new N2SSSlideContainer($slide, $this, $data['cols'], 'default');
        $this->data->un_set('cols');
        $this->data->un_set('inneralign');

        $columns = $this->container->getLayers();

        $columnCount = count($columns);

        for ($i = 0; $i < $columnCount; $i++) {
            /** @var N2SSSlideComponentCol $col */
            $col = $columns[$i];
            $col->updateRowSpecificProperties($this->data->get('desktopportraitgutter'));
        }

        $this->rowAttributes['style'] .= 'padding:' . $this->spacingToEm($this->data->get('desktopportraitpadding')) . ';';

        $this->rowAttributes['style'] .= $this->renderBackground();


        $fullWidth = $this->data->get('fullwidth', 1);
        if (!$fullWidth) {
            $this->attributes['class'] .= ' n2-ss-autowidth';
        }

        $stretch = $this->data->get('stretch', 0);
        if ($stretch) {
            $this->attributes['class'] .= ' n2-ss-stretch-layer';
        }

        $borderRadius = intval($this->data->get('borderradius'));
        if ($borderRadius > 0) {
            $this->rowAttributes['style'] .= 'border-radius:' . $borderRadius . 'px;';
        }

        $boxShadow = explode('|*|', $this->data->get('boxshadow'));
        if (count($boxShadow) == 5 && ($boxShadow[0] != 0 || $boxShadow[1] != 0 || $boxShadow[2] != 0 || $boxShadow[3] != 0) && N2Color::hex2alpha($boxShadow[4]) != 0) {
            $this->rowAttributes['style'] .= 'box-shadow:' . $boxShadow[0] . 'px ' . $boxShadow[1] . 'px ' . $boxShadow[2] . 'px ' . $boxShadow[3] . 'px ' . N2Color::colorToRGBA($boxShadow[4]) . ';';
        }

        $this->placement->attributes($this->attributes);
        $innerAlign = $this->data->get('desktopportraitinneralign');
        if (!empty($innerAlign)) {
            $this->attributes['data-csstextalign'] = $innerAlign;
        }

        $this->createDeviceProperty('padding');
        $this->createDeviceProperty('gutter');
        $this->createDeviceProperty('wrapafter');
        $this->createDeviceProperty('inneralign');
    }

    public function render() {
        if ($this->isRenderAllowed()) {
            $this->prepareHTML();
            $html = N2Html::tag('div', $this->rowAttributes, parent::renderContainer());
            $html = $this->renderPlugins($html);

            return N2Html::tag('div', $this->attributes, $html);
        }

        return '';
    }

    public function admin() {

        $this->createProperty('bgimage');
        $this->createProperty('bgimagex');
        $this->createProperty('bgimagey');
        $this->createProperty('bgimageparallax');
        $this->createProperty('bgcolor');
        $this->createProperty('bgcolorgradient');
        $this->createProperty('bgcolorgradientend');

        $this->createProperty('borderradius');
        $this->createProperty('boxshadow');

        $this->createProperty('fullwidth');
        $this->createProperty('stretch');

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

        N2SmartSliderExport::prepareExportLayer($export, $layer['cols']);
    }

    public static function prepareImport($import, &$layer) {
        if (!empty($layer['bgimage'])) {
            $layer['bgimage'] = $import->fixImage($layer['bgimage']);
        }

        N2SmartSliderImport::prepareImportLayer($import, $layer['cols']);
    }

    public static function prepareSample(&$layer) {
        if (!empty($layer['bgimage'])) {
            $layer['bgimage'] = N2ImageHelper::fixed($layer['bgimage']);
        }

        N2SmartsliderSlidesModel::prepareSample($layer['cols']);
    }

    /**
     * @param N2SmartSliderSlide $slide
     * @param array              $layer
     */
    public static function getFilled($slide, &$layer) {

        $slide->fillLayers($layer['cols']);
    }

}