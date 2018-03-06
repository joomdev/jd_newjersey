<?php

class N2AssetsCss extends N2AssetsAbstract {

    public function __construct() {
        $this->cache = new N2AssetsCacheCSS();
    }

    public function getOutput() {

        N2GoogleFonts::build();
        N2LESS::build();

        $output = "";

        $this->urls = array_unique($this->urls);

        foreach ($this->urls AS $url) {
            $output .= N2Html::style($url, true, array(
                    'media' => 'all'
                )) . "\n";
        }

        $mode = N2Settings::get('css-mode', 'normal');
        if (N2Platform::$isAdmin || $mode == 'normal') {

            foreach ($this->getFiles() AS $file) {
                if (substr($file, 0, 2) == '//') {
                    $output .= N2Html::style($file, true, array(
                            'media' => 'all'
                        )) . "\n";
                } else {
                    $output .= N2Html::style(N2Uri::pathToUri($file, false) . '?' . filemtime($file), true, array(
                            'media' => 'all'
                        )) . "\n";
                }
            }

            $inline = implode("\n", $this->inline);
            if (!empty($inline)) {
                $output .= N2Html::style($inline);
            }
        } else {
            $cssCombine = new N2CacheCombine('css', 'N2AssetsCss::minify');
            foreach ($this->getFiles() AS $file) {
                $cssCombine->add($file);
            }
            $cssCombine->addInline(implode("\n", $this->inline));
            $combinedFile = $cssCombine->make();

            if ($mode == 'combine') {
                $output .= N2Html::style(N2Uri::pathToUri($combinedFile, false), true, array(
                        'media' => 'all'
                    )) . "\n";
            } else if ($mode == 'async') {
                N2JS::addInline('window.n2CSS = "' . N2Uri::pathToUri($combinedFile, false) . '";', true, true);
            } else if ($mode == 'inline') {
                $output .= N2Html::style(file_get_contents($combinedFile), false, array());
            }
        }

        return $output;
    }

    public function get() {
        N2GoogleFonts::build();
        N2LESS::build();

        return array(
            'url'    => $this->urls,
            'files'  => $this->getFiles(),
            'inline' => implode("\n", $this->inline)
        );
    }

    public function getAjaxOutput() {

        $output = implode("\n", $this->inline);

        return $output;
    }

    public static function minify($code) {
        if (!class_exists('csstidy', false)) {
            require_once(dirname(__FILE__) . '/csstidy/class.csstidy.php');
        }

        $csstidy = new csstidy();
        $csstidy->load_template('high_compression');

        $csstidy->parse($code);

        return $csstidy->print->plain();
    }
} 