<div id='n2-ss-slide-canvas-container' class='unselectable'>
    <?php
    include dirname(__FILE__) . '/_toolbar.php';
    ?>
    <div id="n2-ss-slide-canvas-container-inner" class="viewport">
        <div class="n2-ss-scrollbar-wrapper n2-ss-scrollbar-light">
            <div class="scrollbar">
                <div class="track">
                    <div class="thumb"><div class="end"></div></div>
                </div>
            </div>
        </div>

        <div class="n2-ss-slider-real-container">
            <?php echo N2Html::tag('div', array('class' => "n2-ss-slider-outer-container overview"), N2Html::tag('div', array('class' => "n2-ss-canvas-slider-container"), $renderedSlider)); ?>
            <div class="n2-clear"></div>
        </div>


    </div>

    <?php
    include(dirname(__FILE__) . '/_add-sidebar.php');

    N2Localization::addJS(array(
        'Add',
        'Clear',
        'in',
        'loop',
        'out',
        'LOOP',
        'Show',
        'Hide'
    ));

    $options = array(
        'isAddSample'         => $isAddSample,
        'slideAsFile'      => intval(N2SmartSliderSettings::get('slide-as-file', 0)),
        'isUploadDisabled' => defined('N2_IMAGE_UPLOAD_DISABLE')
    );
    if (!defined('N2_IMAGE_UPLOAD_DISABLE')) {
        $options['uploadUrl'] = N2Base::getApplication('system')->router->createAjaxUrl(array('browse/upload'));
        $options['uploadDir'] = 'slider' . $slider->sliderId;
    }

    echo N2Html::script("
            nextend.ready(function($){
                var cb = function(){
                    nextend.smartSlider.slideBackgroundMode = '" . $slider->params->get('backgroundMode', 'fill') . "';
                    nextend.smartSlider.startEditor('" . $slider->elementId . "', 'slideslide', " . json_encode($options) . ");
                };
                if(typeof nextend.fontsDeferred !== 'undefined'){
                    nextend.fontsDeferred.done(cb);
                }else {
                    cb();
                }
            });
        ");
    ?>
</div>