<?php

class N2AssetsCacheLess extends N2AssetsCacheCSS {

    public $outputFileType = "less.css";

    /**
     * @param N2CacheManifest $cache
     *
     * @return string
     */
    public function getCachedContent($cache) {

        $fileContents = '';

        foreach ($this->files AS $parameters) {
            $compiler = new n2lessc();

            if (!empty($parameters['importDir'])) {
                $compiler->addImportDir($parameters['importDir']);
            }

            $compiler->setVariables($parameters['context']);
            $fileContents .= $compiler->compileFile($parameters['file']);
        }

        return $fileContents;
    }

    protected function makeFileHash($parameters) {
        return json_encode($parameters) . filemtime($parameters['file']);
    }

    protected function parseFile($cache, $content, $lessParameters) {

        return parent::parseFile($cache, $content, $lessParameters['file']);
    }
}