<?php

class N2SSSlideComponentLayer extends N2SSSlideComponent {

    protected $type = 'layer';

    /** @var N2SSItemAbstract */
    private $item;

    public function __construct($index, $slide, $group, $data, $placenentType) {


        $this->item = new N2SmartSliderItemsFactory($slide->getSlider(), $slide);
        parent::__construct($index, $slide, $group, $data, $placenentType);


        $this->attributes['style'] = '';

        $item = $this->data->get('item');

        $this->item = N2SmartSliderItemsFactory::create($this, $item);

        $this->placement->attributes($this->attributes);
    }

    public function render() {
        if ($this->isRenderAllowed()) {
            $this->prepareHTML();

            $item = $this->data->get('item');
            if (empty($item)) {
                $items = $this->data->get('items');
                $item  = $items[0];
            }


            if ($this->slide->getSlider()->isAdmin) {
                $renderedItem = $this->item->renderAdmin();
            } else {
                $renderedItem = $this->item->render();
            }

            if ($renderedItem === false) {
                return '';
            }

            if ($this->item->needSize($item)) {
                $this->attributes['class'] .= ' n2-ss-layer-needsize';
            }

            $html = $this->renderPlugins($renderedItem);

            return N2Html::tag('div', $this->attributes, $html);
        }

        return '';
    }

    /**
     * @param N2SmartSliderSlide $slide
     * @param array              $layer
     */
    public static function getFilled($slide, &$layer) {
        if (empty($layer['item'])) {
            $layer['item'] = $layer['items'][0];
            unset($layer['items']);
        }
        N2SmartSliderItemsFactory::getFilled($slide, $layer['item']);
    }

    public static function prepareExport($export, $layer) {
        if (empty($layer['item'])) {
            $layer['item'] = $layer['items'][0];
            unset($layer['items']);
        }

        N2SmartSliderItemsFactory::prepareExport($export, $layer['item']);

    }

    public static function prepareImport($import, &$layer) {
        if (empty($layer['item'])) {
            $layer['item'] = $layer['items'][0];
            unset($layer['items']);
        }

        $layer['item'] = N2SmartSliderItemsFactory::prepareImport($import, $layer['item']);
    }

    public static function prepareSample(&$layer) {
        if (empty($layer['item'])) {
            $layer['item'] = $layer['items'][0];
            unset($layer['items']);
        }

        $layer['item'] = N2SmartSliderItemsFactory::prepareSample($layer['item']);
    }

    public static function translateIds($layers) {
        $idTranslation = array();

        for ($i = 0; $i < count($layers); $i++) {
            if (!empty($layers[$i]['id'])) {
                $newId                            = 'd' . self::uid();
                $idTranslation[$layers[$i]['id']] = $newId;
                $layers[$i]['id']                 = $newId;
            }
            if (isset($layers[$i]['type']) && $layers[$i]['type'] == 'group') {
                for ($j = 0; $j < count($layers[$i]['layers']); $j++) {
                    if (!empty($layers[$i]['layers'][$j]['id'])) {
                        $newId                                          = 'd' . self::uid();
                        $idTranslation[$layers[$i]['layers'][$j]['id']] = $newId;
                        $layers[$i]['layers'][$j]['id']                 = $newId;
                    }
                }
            }
        }

        for ($i = 0; $i < count($layers); $i++) {
            if (!empty($layers[$i]['parentid'])) {
                if (isset($idTranslation[$layers[$i]['parentid']])) {
                    $layers[$i]['parentid'] = $idTranslation[$layers[$i]['parentid']];
                } else {
                    $layers[$i]['parentid'] = '';
                }
            }
            if (isset($layers[$i]['type']) && $layers[$i]['type'] == 'group') {
                for ($j = 0; $j < count($layers[$i]['layers']); $j++) {
                    if (!empty($layers[$i]['layers'][$j]['parentid'])) {
                        if (isset($idTranslation[$layers[$i]['layers'][$j]['parentid']])) {
                            $layers[$i]['layers'][$j]['parentid'] = $idTranslation[$layers[$i]['layers'][$j]['parentid']];
                        } else {
                            $layers[$i]['layers'][$j]['parentid'] = '';
                        }
                    }
                }
            }
        }

        return $layers;
    }

    private static function uid($length = 12) {
        $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString     = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}