<?php
N2Loader::import('libraries.form.element.checkbox');

class N2ElementDevices extends N2ElementHidden {

    public $_tooltip = true;

    function fetchElement() {

        $html = N2Html::tag('div', array(
            'id'    => $this->_id,
            'class' => 'n2-form-element-radio-tab n2-form-element-icon-radio'
        ), $this->generateOptions());

        N2JS::addInline('new N2Classes.FormElementDevices("' . $this->_id . '", ' . json_encode($this->values) . ');');

        return $html;
    }

    function generateOptions() {
        $options = array(
            'desktop-landscape' => 'n2-i n2-it n2-i-desktopLandscape',
            'desktop-portrait'  => 'n2-i n2-it n2-i-desktopPortrait',
            'tablet-landscape'  => 'n2-i n2-it n2-i-tabletLandscape',
            'tablet-portrait'   => 'n2-i n2-it n2-i-tabletPortrait',
            'mobile-landscape'  => 'n2-i n2-it n2-i-mobileLandscape',
            'mobile-portrait'   => 'n2-i n2-it n2-i-mobilePortrait'
        );

        $html = '';
        $i    = 0;
        foreach ($options AS $value => $class) {
            $this->values[] = $value;

            $html .= N2Html::tag('div', array(
                'class' => 'n2-radio-option'
            ), N2Html::tag('i', array(
                    'class' => $class
                )) . N2Html::tag('input', array(
                    'type' => 'hidden',
                    'id'   => $this->_id . '-' . $value
                )));
            $i++;
        }
        return $html;
    }
}