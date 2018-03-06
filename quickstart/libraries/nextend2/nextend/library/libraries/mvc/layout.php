<?php

N2Loader::import("libraries.mvc.view");

class N2Layout extends N2View {

    public $controller = null;

    private $layoutFragments = array();

    private $viewObject = null;

    protected $breadcrumbs = array();

    public function addView($fileName, $position, $viewParameters = array(), $path = null) {
        if (is_null($path)) {
            $controller = strtolower($this->appType->controllerName);
            $path       = $this->appType->path . NDS . "views" . NDS . $controller . NDS;
        }

        if (!file_exists($path . $fileName . ".phtml")) {
            throw new N2ViewException("View file ({$fileName}.phtml) not found in " . $path . $fileName);
        }
        $this->layoutFragments["nextend_" . $position][] = array(
            'params' => $viewParameters,
            'file'   => $path . $fileName . ".phtml"
        );
    }

    /**
     * Render page layout
     *
     * @param string      $fileName
     * @param null|string $path
     * @param array       $params
     *
     * @throws N2ViewException
     */
    protected function renderLayout($fileName, $params = array(), $path = null) {
        if (is_null($path)) {
            $path = $this->appType->path . NDS . "layouts" . NDS;
        } else {
            if (strpos(".", $path) !== false) {
                $path = N2Filesystem::dirFormat($path);
            }
        }

        if (!N2Filesystem::existsFile($path . $fileName . ".phtml")) {
            throw new N2ViewException("Layout file ({$fileName}.phtml) not found in '{$path}'");
        }

        extract($params);

        ob_start();
        /** @noinspection PhpIncludeInspection */
        include $path . $fileName . ".phtml";

        $content = ob_get_clean();

        if (!empty($this->breadcrumbs)) {
            $html = '';
            foreach ($this->breadcrumbs AS $i => $breadcrumb) {
                if ($i) {
                    $html .= N2Html::tag('span', array(), N2Html::tag('i', array('class' => 'n2-i n2-it n2-i-breadcrumbarrow'), ''));
                }
                $html .= $breadcrumb;
            }
            $content = str_replace('<!--breadcrumb-->', N2Html::tag('div', array(
                'class' => 'n2-header-breadcrumbs n2-header-right'
            ), $html), $content);
        }

        echo $content;
    }

    public function render($params = array(), $layoutName = false) {
        $controller = strtolower($this->appType->controllerName);
        $path       = $this->appType->path . NDS . "views" . NDS . $controller . NDS;

        $call = false;
        if (N2Filesystem::existsFile($path . NDS . "_view.php")) {
            require_once $path . NDS . "_view.php";

            $call             = array(
                "class"  => "N2{$this->appType->app->name}{$this->appType->type}{$controller}View",
                "method" => $this->appType->actionName
            );
            $this->viewObject = $this->preCall($call, $this->appType);
        }

        if ($layoutName) {
            $this->renderLayout($layoutName, $params);
        }
    }

    public function renderFragmentBlock($block, $fallback = false) {
        if (isset($this->layoutFragments[$block])) {
            foreach ($this->layoutFragments[$block] as $key => $view) {

                $view["params"]["_class"] = $this->viewObject;
                $this->renderInline($view["file"], $view["params"], null, true);
            }
        } else if ($fallback) {
            $this->renderInline($fallback, array());
        }
    }

    public function getFragmentValue($key, $default = null) {
        if (isset($this->layoutFragments[$key])) {
            return $this->layoutFragments[$key];
        }
        return $default;
    }

    public function addBreadcrumb($html) {
        $this->breadcrumbs[] = $html;
    }

}

class N2LayoutAjax extends N2Layout {

    protected function renderLayout($fileName, $params = array(), $path = null) {
        $this->renderFragmentBlock('nextend_content');
    }
}