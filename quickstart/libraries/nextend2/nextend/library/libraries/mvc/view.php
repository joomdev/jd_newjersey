<?php

class N2View
{

    /**
     * @var N2ApplicationType
     */
    public $appType;

    public $widget;

    public function __construct($appType) {
        $this->appType = $appType;

        $this->widget = new N2EmbedWidget($this);
    }

    public function __get($name) {
        return $this->$name;
    }

    public function __set($name, $value) {
        $this->$name = $value;
    }

    public function renderInline($fileName, $params = array(), $path = null, $absolutePathInFilename = false) {
        if ($absolutePathInFilename) {
            $path = "";
        } elseif (is_null($path)) {
            $path = $this->appType->path . NDS . "inline" . NDS;
        }

        if (strpos($fileName, ".phtml") === false) {
            $fileName = $fileName . ".phtml";
        }

        /**
         * Hack for something
         *
         * @todo write an example, maybe a bug
         */
        /*
        if (strlen($path) < 3 && $path != '') {
            $path = "";
        }
        */
        if (!N2Filesystem::existsFile($path . $fileName)) {
            throw new N2ViewException("View file ({$fileName}) not found in {$path}");
        }

        extract($params);

        /** @noinspection PhpIncludeInspection */
        include $path . $fileName;
    }

    public function renderInlineInNamespace($fileName, $path, $namespace, $params = array(), $absolutePathInFilename = false) {
        $this->renderInline($fileName, $params, N2Loader::toPath($path, $namespace) . NDS, $absolutePathInFilename);
    }


    protected function preCall($preCall, $applicationType = false) {
        if (is_array($preCall)) {
            $class    = $preCall["class"];
            $callable = array(
                null,
                $preCall["method"]
            );

            if (class_exists($class)) {
                $callable[0] = new $class($applicationType, $this->widget);
                /*
                                if ($preCall["viewName"]) {
                                    $callable[0]->viewName = $preCall["viewName"];
                                }*/

                if (is_callable($callable)) {
                    call_user_func($callable, $preCall["viewName"]);
                }

                return $callable[0];
            }
        }

        return false;

    }

}

class N2ViewException extends Exception
{

}