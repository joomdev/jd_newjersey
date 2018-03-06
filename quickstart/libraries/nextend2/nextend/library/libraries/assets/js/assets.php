<?php

/**
 * Class N2AssetsJs
 *
 */
class N2AssetsJs extends N2AssetsAbstract {

    public function __construct() {
        $this->cache = new N2AssetsCacheJS();
    }

    public function getOutput() {

        $output = "";

        $globalInline = $this->getGlobalInlineScripts();
        if (!empty($globalInline)) {
            $output .= N2Html::script(self::minify_js($globalInline . "\n"));
        }

        foreach ($this->urls AS $url) {
            $output .= N2Html::script($url, true) . "\n";
        }

        if (!defined('NEXTEND_CACHE_STORAGE') && !N2Platform::$isAdmin && N2Settings::get('async', '0')) {
            $jsCombined = new N2CacheCombine('js', N2Settings::get('minify-js', '0') ? 'N2MinifierJS::minify' : false);
            foreach ($this->getFiles() AS $file) {
                if (basename($file) == 'n2.js') {
                    $output .= N2Html::script(file_get_contents($file)) . "\n";
                } else {
                    $jsCombined->add($file);
                }
            }
            $combinedFile = $jsCombined->make();
            $scripts      = 'nextend.loadScript("' . N2Uri::pathToUri($combinedFile, false) . '");';
            $output .= N2Html::script(self::minify_js($scripts . "\n"));
        } else {
            if (!defined('NEXTEND_CACHE_STORAGE') && !N2Platform::$isAdmin && N2Settings::get('combine-js', '0')) {
                $jsCombined = new N2CacheCombine('js', N2Settings::get('minify-js', '0') ? 'N2MinifierJS::minify' : false);
                foreach ($this->getFiles() AS $file) {
                    $jsCombined->add($file);
                }
                $combinedFile = $jsCombined->make();

                if (substr($combinedFile, 0, 2) == '//') {
                    $output .= N2Html::script($combinedFile, true) . "\n";
                } else {
                    $output .= N2Html::script(N2Uri::pathToUri($combinedFile, false), true) . "\n";
                }
            } else {
                foreach ($this->getFiles() AS $file) {
                    if (substr($file, 0, 2) == '//') {
                        $output .= N2Html::script($file, true) . "\n";
                    } else {
                        $output .= N2Html::script(N2Uri::pathToUri($file, false) . '?' . filemtime($file), true) . "\n";
                    }
                }
            }
        }

        $output .= N2Html::script(self::minify_js(N2Localization::toJS() . "\n" . $this->getInlineScripts() . "\n"));

        return $output;
    }

    public function get() {
        return array(
            'url'          => $this->urls,
            'files'        => $this->getFiles(),
            'inline'       => $this->getInlineScripts(),
            'globalInline' => $this->getGlobalInlineScripts()
        );
    }

    public function getAjaxOutput() {

        $output = $this->getInlineScripts();

        return $output;
    }

    private function getGlobalInlineScripts() {
        return implode('', $this->globalInline);
    }

    private function getInlineScripts() {
        $scripts = '';

        foreach ($this->firstCodes AS $script) {
            $scripts .= $script . "\n";
        }

        foreach ($this->inline AS $script) {
            $scripts .= $script . "\n";
        }

        return $this->serveJquery($scripts);
    }

    public static function serveJquery($script) {
        if (empty($script)) {
            return "";
        }
        $inline = "window.n2jQuery.ready((function($){\n";
        $inline .= "\twindow.nextend.ready(function() {\n";
        $inline .= $script;
        $inline .= "\t});\n";
        $inline .= "}));\n";

        return $inline;
    }

    public static function minify_js($input) {
        if (trim($input) === "") return $input;

        return preg_replace(array(
            // Remove comment(s)
            '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
            // Remove white-space(s) outside the string and regex
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
            // Remove the last semicolon
            '#;+\}#',
            // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
            '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
            // --ibid. From `foo['bar']` to `foo.bar`
            '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
        ), array(
            '$1',
            '$1$2',
            '}',
            '$1$3',
            '$1.$3'
        ), $input);
    }
} 