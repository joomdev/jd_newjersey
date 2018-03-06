<?php

class N2SmartSliderFrontendSliderPreRenderController extends N2Controller {

    public function initialize() {
        N2JS::jQuery(true, true);

        parent::initialize();

        N2Loader::import(array(
            'models.Sliders',
            'models.Slides'
        ), 'smartslider');

    }

    public function actionIframe() {

        $sliderID = isset($_GET['sliderid']) ? intval($_GET['sliderid']) : false;
        if (!$sliderID) throw new Exception('Slider ID is not valid.');
        N2CSS::addStaticGroup(N2LIBRARYASSETS . '/normalize.min.css', 'normalize');
    


        $locale = setlocale(LC_NUMERIC, 0);
        setlocale(LC_NUMERIC, "C");

        $sliderManager = new N2SmartSliderManager($sliderID);
        $slider        = $sliderManager->render(true);

        setlocale(LC_NUMERIC, $locale);

        $this->addView("iframe", array(
            "slider" => $slider
        ), "content");

        $this->render();
    }

} 