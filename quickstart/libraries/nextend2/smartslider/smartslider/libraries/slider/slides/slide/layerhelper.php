<?php

class N2SmartSliderLayerHelper {

    public $data = array(
        "eye"                         => false,
        "lock"                        => false,
        "animations"                  => array(
            "specialZeroIn"       => 0,
            "transformOriginIn"   => "50|*|50|*|0",
            "inPlayEvent"         => "",
            "repeatCount"         => 0,
            "repeatStartDelay"    => 0,
            "transformOriginLoop" => "50|*|50|*|0",
            "loopPlayEvent"       => "",
            "loopPauseEvent"      => "",
            "loopStopEvent"       => "",
            "transformOriginOut"  => "50|*|50|*|0",
            "outPlayEvent"        => "",
            "instantOut"          => 1,
            "in"                  => array(),
            "loop"                => array(),
            "out"                 => array()
        ),
        "id"                          => null,
        "parentid"                    => null,
        "name"                        => "Layer",
        "namesynced"                  => 1,
        "crop"                        => "visible",
        "inneralign"                  => "left",
        "parallax"                    => 0,
        "adaptivefont"                => 0,
        "desktopportrait"             => 1,
        "desktoplandscape"            => 1,
        "tabletportrait"              => 1,
        "tabletlandscape"             => 1,
        "mobileportrait"              => 1,
        "mobilelandscape"             => 1,
        "responsiveposition"          => 1,
        "responsivesize"              => 1,
        "desktopportraitleft"         => 0,
        "desktopportraittop"          => 0,
        "desktopportraitwidth"        => "auto",
        "desktopportraitheight"       => "auto",
        "desktopportraitalign"        => "center",
        "desktopportraitvalign"       => "middle",
        "desktopportraitparentalign"  => "center",
        "desktopportraitparentvalign" => "middle",
        "desktopportraitfontsize"     => 100,
        "items"                       => array()

    );

    public function __construct($properties = array()) {
        foreach ($properties as $k => $v) {
            $this->data[$k] = $v;
        }
    }

    public function set($key, $value) {
        $this->data[$key] = $value;

        return $this;
    }
}
