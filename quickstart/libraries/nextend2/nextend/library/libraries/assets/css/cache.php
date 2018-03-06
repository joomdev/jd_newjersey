<?php

class N2AssetsCacheCSS extends N2AssetsCache {

    public $outputFileType = "css";

    private $baseUrl = '', $basePath = '';

    public function getAssetFileFolder() {
        return N2Filesystem::getWebCachePath() . NDS . $this->group . NDS;
    }

    protected function parseFile($cache, $content, $originalFilePath) {

        $this->basePath = dirname($originalFilePath);
        $this->baseUrl  = N2Filesystem::pathToAbsoluteURL($this->basePath);

        if (!$cache->storage->isFilesystem()) {

            return preg_replace_callback('#url\([\'"]?([^"\'\)]+)[\'"]?\)#', array(
                $this,
                'makeAbsoluteUrl'
            ), $content);
        }

        return preg_replace_callback('#url\([\'"]?([^"\'\)]+)[\'"]?\)#', array(
            $this,
            'makeRelativeUrl'
        ), $content);
    }

    private function makeRelativeUrl($matches) {
        if (substr($matches[1], 0, 5) == 'data:') return $matches[0];
        if (substr($matches[1], 0, 4) == 'http') return $matches[0];
        if (substr($matches[1], 0, 2) == '//') return $matches[0];

        $exploded = explode('?', $matches[1]);

        $realPath = realpath($this->basePath . '/' . $exploded[0]);
        if ($realPath === false) {
            return 'url(' . str_replace(array(
                    'http://',
                    'https://'
                ), '//', $this->baseUrl) . '/' . $matches[1] . ')';
        }

        $realPath  = N2Filesystem::fixPathSeparator($realPath);
        $assetPath = N2Filesystem::fixPathSeparator($this->getAssetFileFolder());

        return 'url(' . N2Filesystem::toLinux($this->find_relative_path($assetPath, $realPath)) . (isset($exploded[1]) ? '?' . $exploded[1] : '') . ')';
    }

    private function makeAbsoluteUrl($matches) {
        if (substr($matches[1], 0, 5) == 'data:') return $matches[0];
        if (substr($matches[1], 0, 4) == 'http') return $matches[0];
        if (substr($matches[1], 0, 2) == '//') return $matches[0];

        $exploded = explode('?', $matches[1]);

        $realPath = realpath($this->basePath . '/' . $exploded[0]);
        if ($realPath === false) {
            return 'url(' . str_replace(array(
                    'http://',
                    'https://'
                ), '//', $this->baseUrl) . '/' . $matches[1] . ')';
        }

        $realPath = N2Filesystem::fixPathSeparator($realPath);

        return 'url(' . N2Uri::pathToUri($realPath, false) . (isset($exploded[1]) ? '?' . $exploded[1] : '') . ')';
    }

    private function find_relative_path($frompath, $topath) {
        $from    = explode(DIRECTORY_SEPARATOR, $frompath); // Folders/File
        $to      = explode(DIRECTORY_SEPARATOR, $topath); // Folders/File
        $relpath = '';

        $i = 0;
        // Find how far the path is the same
        while (isset($from[$i]) && isset($to[$i])) {
            if ($from[$i] != $to[$i]) break;
            $i++;
        }
        $j = count($from) - 1;
        // Add '..' until the path is the same
        while ($i <= $j) {
            if (!empty($from[$j])) $relpath .= '..' . DIRECTORY_SEPARATOR;
            $j--;
        }
        // Go to folder from where it starts differing
        while (isset($to[$i])) {
            if (!empty($to[$i])) $relpath .= $to[$i] . DIRECTORY_SEPARATOR;
            $i++;
        }

        // Strip last separator
        return substr($relpath, 0, -1);
    }
}