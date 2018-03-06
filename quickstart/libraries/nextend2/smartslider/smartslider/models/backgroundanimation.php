<?php
N2Loader::import(array(
    'libraries.backgroundanimation.storage'
), 'smartslider');

class N2SmartSliderBackgroundAnimationModel extends N2SystemVisualModel
{

    public $type = 'backgroundanimation';

    public function __construct($tableName = null) {

        parent::__construct($tableName);
        $this->storage = N2Base::getApplication('smartslider')->storage;
    }

    protected function getPath() {
        return dirname(__FILE__);
    }
}