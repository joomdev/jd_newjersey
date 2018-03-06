<?php
N2Loader::import('libraries.plugins.N2SliderGeneratorPluginAbstract', 'smartslider');

class N2SSPluginGeneratorJoomlaContent extends N2PluginBase
{

    public static $group = 'joomlacontent';
    public static $groupLabel = 'Joomla content';

    function onGeneratorList(&$group, &$list) {
        $group[self::$group] = self::$groupLabel;

        if (!isset($list[self::$group])) {
            $list[self::$group] = array();
        }

        $list[self::$group]['article'] = N2GeneratorInfo::getInstance(self::$groupLabel, n2_('Articles'), $this->getPath() . 'article')
                                                        ->setType('article');

        $list[self::$group]['category'] = N2GeneratorInfo::getInstance(self::$groupLabel, n2_('Categories'), $this->getPath() . 'category')
                                                         ->setType('article');
    }

    function getPath() {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR;
    }

}

N2Plugin::addPlugin('ssgenerator', 'N2SSPluginGeneratorJoomlaContent');