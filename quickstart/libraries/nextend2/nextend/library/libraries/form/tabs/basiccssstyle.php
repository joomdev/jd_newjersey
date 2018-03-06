<?php

class N2TabBasicCSSStyle extends N2Tab {

    protected function _renderTitle() {
        echo N2Html::tag('div', array(
            'class' => 'n2-h2'
        ), N2Html::tag('span', array(
                'class' => 'n2-css-name'
            ), N2Html::tag('span', array(
                    'class' => 'n2-css-name-label n2-uc'
                ), '') . N2Html::tag('span', array(
                    'class' => 'n2-css-name-list'
                ), '')) . N2Html::tag('div', array(
                'class' => 'n2-css-tab'
            )) . N2Html::tag('div', array(
                'class' => 'n2-css-tab-reset n2-button n2-button-icon n2-button-s n2-radius-s n2-button-grey',
                'data-n2tip' => n2_('Reset to normal state'),
            ), '<i class="n2-i n2-i-reset2"></i>'));
    }

    function decorateGroupStart() {
    }

    function decorateGroupEnd() {
        echo N2Html::link(n2_('More'), '#', array(
            'class' => 'n2-basiccss-more n2-button n2-button-normal n2-button-s n2-button-grey n2-radius-s n2-h5 n2-uc'
        ));
        echo "</div>";
    }

    function decorateElement(&$el, $out, $i) {
        echo N2Html::tag('div', array(
            'class' => 'n2-inline-block ' . N2XmlHelper::getAttribute($el->_xml, 'class')
        ), N2Html::tag('div', array(
            'class' => 'n2-form-element-mixed'
        ), N2Html::tag('div', array(
                'class' => 'n2-mixed-label'
            ), $out[0]) . N2Html::tag('div', array(
                'class' => 'n2-mixed-element'
            ), $out[1])));


    }
}