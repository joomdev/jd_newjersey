<?php
N2Form::importElement('hidden');

class N2ElementItems extends N2ElementHidden {


    function fetchElement() {
        /** @var N2SSPluginItemFactoryAbstract[] $items */
        $items = N2SmartSliderItemsFactory::getItems();
        ob_start();
        ?>
        <div id="smartslider-slide-toolbox-item" class="nextend-clearfix smartslider-slide-toolbox-view">
            <?php
            $itemModel = new N2SmartsliderItemModel();

            $slider = N2Base::getApplication('smartslider')
                            ->get('sliderManager')
                            ->getSlider();

            foreach ($items AS $type => $item) {
                $item->loadResources($slider);

                echo N2Html::openTag("div", array(
                    "id"              => "smartslider-slide-toolbox-item-type-{$type}",
                    "style"           => "display:none",
                    "data-itemvalues" => json_encode($item->getValues())
                ));
                $itemModel->renderForm($type, $item->getPath() . 'configuration.xml');
                echo N2Html::closeTag("div");
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }
}