<?php


class N2ViewBase
{

    /** @var  N2ApplicationType */
    public $appType;
    /** @var  N2EmbedWidget */
    public $widget;

    public function __construct($appType, $widget) {
        $this->appType = $appType;
        $this->widget  = $widget;
    }
}
