<?php

N2Loader::import('libraries.form.element.text');
N2Loader::import('libraries.browse.browse');

N2ImageHelper::init();

N2Loader::import('libraries.image.aviary');

class N2ElementVideo extends N2ElementText {

    function fetchElement() {
        $html = parent::fetchElement();

        $params = array();

        N2ImageHelper::initLightbox();

        N2JS::addInline("new N2Classes.FormElementImage('" . $this->_id . "', " . json_encode($params) . " );");

        return $html;
    }

    protected function post() {
        return N2Html::tag('a', array(
                'href'  => '#',
                'class' => 'n2-form-element-clear'
            ), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-empty n2-i-grey-opacity'), '')) . '<a id="' . $this->_id . '_button" class="n2-form-element-button n2-icon-button n2-h5 n2-uc" href="#"><i class="n2-i n2-it  n2-i-layer-image"></i></a>';
    }

    protected function getClass() {
        return 'n2-form-element-img';
    }
}
