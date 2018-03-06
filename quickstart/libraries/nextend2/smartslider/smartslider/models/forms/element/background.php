<?php

class N2ElementBackground extends N2ElementHidden {

    private $options;

    function fetchElement() {
        $this->options = array(
            'image' => 'Image',
            'color' => 'Color'
        );
    

        N2JS::addInline('new N2Classes.FormElementBackground("' . $this->_id . '", "' . $this->getValue() . '");');


        $html = '<div id="' . $this->_id . '-panel" class="n2-subform-image">';
        foreach ($this->options AS $k => $value) {
            $html .= $this->getOptionHtml('$ss$/admin/images/background/', $k, $value);
        }
        $html .= '</div>';
        return $html . parent::fetchElement();
    }

    function getOptionHtml($path, $k, $label) {
        return N2Html::tag('div', array(
            'class'      => 'n2-subform-image-option ' . $this->isActive($k),
            'data-value' => $k
        ), N2Html::tag('div', array(
                'class' => 'n2-subform-image-element',
                'style' => 'background-image: URL(' . $this->getImage($path, $k) . ');'
            )) . N2Html::tag('div', array(
                'class' => 'n2-subform-image-title n2-h4'
            ), $label));
    }

    function getImage($path, $key) {
        return N2ImageHelper::fixed($path . $key . '.png');
    }

    function isActive($value) {
        if ($this->getValue() == $value) {
            return 'n2-active';
        }
        return '';
    }
}