<?php

class  N2SSSlidePlacementNormal extends N2SSSlidePlacement {

    public function attributes(&$attributes) {
        $data = $this->component->data;

        $attributes['data-pm'] = 'normal';

        $attributes['style'] .= 'margin:' . $this->component->spacingToEm($data->get('desktopportraitmargin')) . ';';
        $this->component->createDeviceProperty('margin');

        $height = $data->get('desktopportraitheight');
        if ($height > 0) {
            $attributes['style'] .= 'height:' . $this->component->pxToEm($data->get('desktopportraitheight')) . ';';
        }
        $this->component->createDeviceProperty('height');


        $maxWidth = intval($data->get('desktopportraitmaxwidth'));
        if ($maxWidth > 0) {
            $attributes['style'] .= 'max-width: ' . $maxWidth . 'px;';
            $attributes['class'] .= ' n2-ss-has-maxwidth ';
        }
        $this->component->createDeviceProperty('maxwidth');


        $attributes['data-cssselfalign'] = $data->get('desktopportraitselfalign');
        $this->component->createDeviceProperty('selfalign');

    }

    public function adminAttributes(&$attributes) {
    }
}