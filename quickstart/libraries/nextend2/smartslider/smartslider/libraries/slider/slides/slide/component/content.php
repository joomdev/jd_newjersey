<?php

class N2SSSlideComponentContent extends N2SSSlideComponent {

    protected $type = 'content';

    protected $colAttributes = array(
        'class' => 'n2-ss-section-main-content n2-ss-layer-content n2-ow',
        'style' => ''
    );

    protected $outerSectionAttributes = array(
        'class' => 'n2-ss-section-outer'
    );

    public function __construct($index, $slide, $group, $data, $placenentType) {
        parent::__construct($index, $slide, $group, $data, 'content');
        $this->container = new N2SSSlideContainer($slide, $this, $data['layers'], 'normal');
        $this->data->un_set('layers');

        $this->attributes['style'] = '';

        $innerAlign = $this->data->get('desktopportraitinneralign');
        if (!empty($innerAlign)) {
            $this->attributes['data-csstextalign'] = $innerAlign;
        }

        $this->colAttributes['data-verticalalign'] = $this->data->get('verticalalign');

        $this->colAttributes['style'] .= 'padding:' . $this->spacingToEm($this->data->get('desktopportraitpadding')) . ';';

        $this->outerSectionAttributes['style'] = $this->renderBackground();

        $maxWidth = intval($this->data->get('desktopportraitmaxwidth'));
        if ($maxWidth > 0) {
            $this->attributes['style'] .= 'max-width: ' . $maxWidth . 'px;';
            $this->attributes['class'] .= ' n2-ss-has-maxwidth ';
        }
        $this->createDeviceProperty('maxwidth');

        $this->attributes['data-cssselfalign'] = $this->data->get('desktopportraitselfalign');

        $this->createDeviceProperty('selfalign');


        $this->placement->attributes($this->attributes);

        $this->createDeviceProperty('padding');
        $this->createDeviceProperty('inneralign');

    }

    public function updateRowSpecificProperties($gutter, $width, $isLast) {
        $this->attributes['style'] .= 'width: ' . $width . '%;';

        if (!$isLast) {
            $this->attributes['style'] .= 'margin-right: ' . $gutter . 'px;margin-bottom: ' . $gutter . 'px;';
        }

    }

    public function render() {
        if ($this->isRenderAllowed()) {
            if (N2SSSlideComponent::$isAdmin || count($this->container->getLayers())) {
                $this->prepareHTML();
                $html = N2Html::tag('div', $this->colAttributes, parent::renderContainer());
                $html = $this->renderPlugins($html);

                return N2Html::tag('div', $this->outerSectionAttributes, N2Html::tag('div', $this->attributes, $html));
            }
        }

        return '';

    }

    public function admin() {

        $this->createProperty('verticalalign');

        $this->createProperty('bgimage');
        $this->createProperty('bgimagex');
        $this->createProperty('bgimagey');
        $this->createProperty('bgimageparallax');
        $this->createProperty('bgcolor');
        $this->createProperty('bgcolorgradient');
        $this->createProperty('bgcolorgradientend');

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