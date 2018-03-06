<?php

class N2SmartSliderCSSSimple extends N2SmartSliderCSSAbstract
{

    protected function  renderType(&$context) {
        $params = $this->slider->params;
        N2Loader::import('libraries.image.color');

        $width  = intval($context['width']);
        $height = intval($context['height']);

        $context['backgroundSize']       = $params->get('background-size');
        $context['backgroundAttachment'] = $params->get('background-fixed') ? 'fixed' : 'scroll';

        $borderWidth             = $params->get('border-width');
        $borderColor             = $params->get('border-color');
        $context['borderRadius'] = $params->get('border-radius') . 'px';

        $padding             = N2Parse::parse($params->get('padding'));
        $context['paddingt'] = $padding[0] . 'px';
        $context['paddingr'] = $padding[1] . 'px';
        $context['paddingb'] = $padding[2] . 'px';
        $context['paddingl'] = $padding[3] . 'px';

        if ($context['canvas']) {
            $width += 2 * $borderWidth + $padding[1] + $padding[3];
            $height += 2 * $borderWidth + $padding[0] + $padding[2];

            $context['width']  = $width . "px";
            $context['height'] = $height . "px";
        }


        $context['border'] = $borderWidth . 'px';

        $rgba                  = N2Color::hex2rgba($borderColor);
        $context['borderrgba'] = 'RGBA(' . $rgba[0] . ',' . $rgba[1] . ',' . $rgba[2] . ',' . round($rgba[3] / 127, 2) . ')';
        $context['borderhex']  = '#' . substr($borderColor, 0, 6);

        $width                   = $width - ($padding[1] + $padding[3]) - $borderWidth * 2;
        $height                  = $height - ($padding[0] + $padding[2]) - $borderWidth * 2;
        $context['inner1height'] = $height . 'px';

        $context['canvaswidth']  = $width . "px";
        $context['canvasheight'] = $height . "px";

        N2LESS::addFile(N2Filesystem::translate(dirname(__FILE__) . NDS . 'style.n2less'), $this->slider->cacheId, $context, NEXTEND_SMARTSLIDER_ASSETS . '/less' . NDS);
    }

}