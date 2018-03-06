<?php
N2Loader::import(array(
    'libraries.embedwidget.interface'
));

class N2EmbedWidget {

    /**
     * @var N2View
     */
    protected $viewObject;

    public function __construct($viewObject) {
        $this->viewObject = $viewObject;
    }

    public function init($widgetName, $params = array(), $path = null) {
        $widgetName           = strtolower($widgetName);
        $params["widgetPath"] = ($path ? $path : self::getEmbedWidgetPath()) . $widgetName . NDS;

        $class = 'N2' . ucfirst($widgetName);
        
        if (!class_exists($class, false)) {
            require_once $params["widgetPath"] . ucfirst($widgetName) . ".php";
        }

        call_user_func(array(
            new $class($this->viewObject),
            "run"
        ), $params);
    }

    public function getEmbedWidgetPath() {
        return N2LIBRARY . NDS . "embedwidgets" . NDS;
    }

    protected function render($viewParams) {
        $this->viewObject->renderInline("view", $viewParams, $viewParams["widgetPath"] . "views" . NDS);
    }

}