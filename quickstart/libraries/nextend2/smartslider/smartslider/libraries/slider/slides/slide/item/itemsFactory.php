<?php
N2Loader::import('libraries.slider.slides.slide.item.itemAbstract', 'smartslider');

class N2SmartSliderItemsFactory {

    public static $i = array();

    public $slider, $slide;

    /** @var N2SSPluginItemFactoryAbstract[][] */
    private static $itemGroups = array();

    /**
     * @var N2SSPluginItemFactoryAbstract[]
     */
    private static $items = array();

    private static function _load() {
        static $loaded;
        if (!$loaded) {
            N2Plugin::callPlugin('ssitem', 'onLoadItems', array(&self::$items));
            self::sortItems();

            /** @var N2SSPluginItemFactoryAbstract[][] $groups */
            self::$itemGroups = array(
                'Basic' => array()
            );

            foreach (self::$items as $type => $item) {
                $group = $item->getGroup();
                if (!isset(self::$itemGroups[$group])) {
                    self::$itemGroups[$group] = array();
                }
                self::$itemGroups[$group][$type] = $item;
            }

            $loaded = true;
        }
    }

    /**
     * @return N2SSPluginItemFactoryAbstract[]
     */
    public static function getItems() {
        self::_load();

        return self::$items;
    }

    /**
     * @return N2SSPluginItemFactoryAbstract[][]
     */
    public static function getItemGroups() {
        self::_load();

        return self::$itemGroups;
    }

    /**
     * @param N2SSSlideComponentLayer $layer
     * @param array                   $itemData
     *
     * @return N2SSItemAbstract
     * @throws Exception
     */
    public static function create($layer, $itemData) {
        self::_load();
        if (!isset($itemData['type'])) {
            throw new Exception('Error with itemData: ' . $itemData);
        }

        $type = $itemData['type'];
        if (!isset(self::$items[$type])) {
            throw new Exception('Missing layer type: ' . $type . '. This layer type is only available in PRO version.');
        }

        /** @var N2SSPluginItemFactoryAbstract $factory */
        $factory = self::$items[$type];
        $class   = $factory->getClass();
        if (class_exists($class)) {

            $slider = $layer->getSlide()
                            ->getSlider();
            self::$i[$slider->elementId]++;
            $id = $slider->elementId . 'item' . self::$i[$slider->elementId];

            return new $class($id, $itemData['values'], $layer);
        }

        throw new Exception('Missing ' . $type . ' class:' . $class);
    }

    /**
     * @param $slider N2SmartSliderAbstract
     * @param $slide  N2SmartSliderSlide
     */
    public function __construct($slider, $slide) {
        self::_load();

        $this->slider = $slider;
        $this->slide  = $slide;

        if (!isset(self::$i[$slider->elementId])) {
            self::$i[$slider->elementId] = 0;
        }

    }

    /**
     * @param N2SmartSliderSlide $slide
     * @param array              $item
     */
    public static function getFilled($slide, &$item) {
        self::_load();
        $type = $item['type'];
        if (isset(self::$items[$type])) {
            $item['values'] = self::$items[$type]->getFilled($slide, new N2Data($item['values']))
                                                 ->toArray();
        }
    }

    /**
     * @param N2SmartSliderExport      $export
     * @param                          $item
     */
    public static function prepareExport($export, $item) {
        self::_load();
        $type = $item['type'];
        if (isset(self::$items[$type])) {
            self::$items[$type]->prepareExport($export, new N2Data($item['values']));
        }
    }

    /**
     * @param N2SmartSliderImport      $import
     * @param                          $item
     *
     * @return mixed
     */
    public static function prepareImport($import, $item) {
        self::_load();
        $type = $item['type'];
        if (isset(self::$items[$type])) {
            $item['values'] = self::$items[$type]->prepareImport($import, new N2Data($item['values']))
                                                 ->toArray();
        }

        return $item;
    }

    public static function prepareSample($item) {
        self::_load();
        $type = $item['type'];
        if (isset(self::$items[$type])) {
            $item['values'] = self::$items[$type]->prepareSample(new N2Data($item['values']))
                                                 ->toArray();
        }

        return $item;
    }

    private static function sortItems() {
        uasort(self::$items, array(
            'N2SmartSliderItemsFactory',
            'compareItems'
        ));
    }

    /**
     * @param N2SSPluginItemFactoryAbstract $a
     * @param N2SSPluginItemFactoryAbstract $b
     *
     * @return int
     */
    private static function compareItems($a, $b) {
        return ($a->getPriority() < $b->getPriority()) ? -1 : 1;
    }
}
