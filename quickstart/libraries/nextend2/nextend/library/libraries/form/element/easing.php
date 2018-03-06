<?php
N2Loader::import('libraries.form.element.list');

class N2ElementEasing extends N2ElementList
{

    function fetchElement() {
        $easings = array(
            "dojo.fx.easing.linear"      => "Linear",
            "dojo.fx.easing.quadIn"      => "Quad_In",
            "dojo.fx.easing.quadOut"     => "Quad_Out",
            "dojo.fx.easing.quadInOut"   => "Quad_In_Out",
            "dojo.fx.easing.cubicIn"     => "Cubic_In",
            "dojo.fx.easing.cubicOut"    => "Cubic_Out",
            "dojo.fx.easing.cubicInOut"  => "Cubic_In_Out",
            "dojo.fx.easing.quartIn"     => "Quart_In",
            "dojo.fx.easing.quartOut"    => "Quart_Out",
            "dojo.fx.easing.quartInOut"  => "Quart_In_Out",
            "dojo.fx.easing.quintIn"     => "Quint_In",
            "dojo.fx.easing.quintOut"    => "Quint_Out",
            "dojo.fx.easing.quintInOut"  => "Quint_In_Out",
            "dojo.fx.easing.sineIn"      => "Sine_In",
            "dojo.fx.easing.sineOut"     => "Sine_Out",
            "dojo.fx.easing.sineInOut"   => "Sine_In_Out",
            "dojo.fx.easing.expoIn"      => "Expo_In",
            "dojo.fx.easing.expoOut"     => "Expo_Out",
            "dojo.fx.easing.expoInOut"   => "Expo_In_Out",
            "dojo.fx.easing.circIn"      => "Circ_In",
            "dojo.fx.easing.circOut"     => "Circ_Out",
            "dojo.fx.easing.circInOut"   => "Circ_In_Out",
            "dojo.fx.easing.backIn"      => "Back_In",
            "dojo.fx.easing.backOut"     => "Back_Out",
            "dojo.fx.easing.backInOut"   => "Back_In_Out",
            "dojo.fx.easing.bounceIn"    => "Bounce_In",
            "dojo.fx.easing.bounceOut"   => "Bounce_Out",
            "dojo.fx.easing.bounceInOut" => "Bounce_In_Out"
        );
        foreach ($easings as $k => $easing) {
            $this->_xml->addChild('option', n2_($easing))->addAttribute('value', $k);
        }
        return parent::fetchElement();
    }
}
