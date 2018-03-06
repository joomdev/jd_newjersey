<?php
N2Loader::import('libraries.form.tab');

class N2TabTabbed extends N2Tab {

    function render($control_name) {
        $this->initTabs();

        $id = 'n2-form-matrix-' . $this->_name;

        $active = $this->getActive();

        $underlined = N2XmlHelper::getAttribute($this->_xml, 'underlined');

        $classes = N2XmlHelper::getAttribute($this->_xml, 'classes');
        ?>

        <div id="<?php echo $id; ?>" class="n2-form-tab n2-form-matrix">

            <?php if (N2XmlHelper::getAttribute($this->_xml, 'external') != 1): ?>
                <div
                    class="n2-h2 n2-content-box-title-bg n2-form-matrix-views <?php echo $classes; ?>">
                <?php
                $i     = 0;
                $class = ($underlined ? 'n2-underline' : '') . ' n2-h4 n2-uc n2-has-underline n2-form-matrix-menu';


                foreach ($this->_tabs AS $tabName => $tab) {


                    echo N2Html::tag("div", array(
                        "class"    => $class . ($i == $active ? ' n2-active' : '') . ' n2-fm-' . $tabName,
                        "data-tab" => $tabName
                    ), N2Html::tag("span", array("class" => "n2-underline"), n2_(N2XmlHelper::getAttribute($tab->_xml, 'label'))));

                    $i++;
                }
                ?>
            </div>
            <?php endif; ?>

            <div class="n2-tabs">
                <?php
                $i = 0;
                foreach ($this->_tabs AS $tabName => $tab) {
                    echo N2Html::openTag('div', array(
                        'class' => 'n2-form-matrix-pane' . ($i == $active ? ' n2-active' : '') . ' n2-fm-' . $tabName
                    ));
                    $tab->render($control_name);
                    echo N2Html::closeTag('div');
                    $i++;
                }
                ?>
            </div>
        </div>
        <?php
        $this->addScript($id);
    }

    protected function getActive() {
        $active = intval(N2XmlHelper::getAttribute($this->_xml, 'active'));
        return $active > 0 ? $active - 1 : 0;
    }

    protected function addScript($id) {
        N2JS::addInline('
            (function(){
                var matrix = $("#' . $id . '"),
                    views = matrix.find("> .n2-form-matrix-views > div"),
                    panes = matrix.find("> .n2-tabs > div");
                views.on("click", function(){
                    views.removeClass("n2-active");
                    panes.removeClass("n2-active");
                    var i = views.index(this);
                    views.eq(i).addClass("n2-active");
                    panes.eq(i).addClass("n2-active");
                });
            })();
        ');
    }

}
