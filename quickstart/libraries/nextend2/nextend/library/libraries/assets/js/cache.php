<?php

class N2AssetsCacheJS extends N2AssetsCache
{

    public $outputFileType = "js";

    protected function createInlineCode($group, &$codes) {
        return N2AssetsJs::serveJquery(parent::createInlineCode($group, $codes));
    }
}