<?php
N2Loader::import('libraries.form.tab');

class N2TabTabbedSidebar extends N2Tab {

    var $_tabs;

    function initTabs() {
        if (count($this->_tabs) == 0) {
            foreach ($this->_xml->params as $tab) {
                $test = N2XmlHelper::getAttribute($tab, 'test');
                if ($test == '' || $this->_form->makeTest($test)) {
                    $type = N2XmlHelper::getAttribute($tab, 'type');
                    if ($type == '') $type = 'default';
                    N2Loader::import('libraries.form.tabs.' . $type);
                    $class = 'N2Tab' . ucfirst($type);

                    $this->_tabs[N2XmlHelper::getAttribute($tab, 'name')] = new $class($this->_form, $tab);
                }
            }

            N2Pluggable::doAction('N2TabTabbedSidebar' . N2XmlHelper::getAttribute($this->_xml, 'name'), array(
                $this
            ));
        }
    }

    public function addTabXML($file, $position = 2) {
        $xml = simplexml_load_string(file_get_contents($file));

        foreach ($xml->params as $tab) {
            $test = N2XmlHelper::getAttribute($tab, 'test');
            if ($test == '' || $this->_form->makeTest($test)) {
                $type = N2XmlHelper::getAttribute($tab, 'type');
                if ($type == '') $type = 'default';
                N2Loader::import('libraries.form.tabs.' . $type);
                $class = 'N2Tab' . ucfirst($type);

                $position = N2XmlHelper::getAttribute($tab, 'position');
                if ($position != '' && $position >= 0) {
                    $a                                          = array();
                    $a[N2XmlHelper::getAttribute($tab, 'name')] = new $class($this->_form, $tab);
                    $this->_tabs                                = self::array_insert($this->_tabs, $a, $position);
                } else {
                    $this->_tabs[N2XmlHelper::getAttribute($tab, 'name')] = new $class($this->_form, $tab);
                }
            }
        }
    }

    private function array_insert($array, $values, $offset) {
        return array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset, NULL, true);
    }

    function render($control_name) {
        $this->initTabs();

        $count  = count($this->_tabs);
        $id     = 'n2-tabbed-' . $this->_name;
        $active = intval(N2XmlHelper::getAttribute($this->_xml, 'active'));
        $active = $active > 0 ? $active - 1 : 0;

        $underlined = N2XmlHelper::getAttribute($this->_xml, 'underlined');

        ?>

        <div id="<?php echo $id; ?>">
            <div
                class="n2-table n2-table-fixed n2-labels <?php echo N2XmlHelper::getAttribute($this->_xml, 'classes') . ($underlined ? ' n2-has-underline' : ''); ?>">
                <div class="n2-tr">
                    <?php
                    $i = 0;
                    foreach ($this->_tabs AS $tabname => $tab) {
                        echo N2Html::tag('div', array(
                            'data-tab' => N2XmlHelper::getAttribute($tab->_xml, 'name'),
                            'class'    => "n2-td n2-h3 n2-uc n2-has-underline" . ($i == $active ? ' n2-active' : '')
                        ), $this->getLabel($tab, $underlined));
                        $i++;
                    }
                    ?>
                </div>
            </div>
            <div class="n2-tabs">
                <?php
                $tabs = array();
                $i    = 0;
                foreach ($this->_tabs AS $tabname => $tab) {
                    $display = 'none';
                    if ($i == $active) {
                        $display = 'block';
                    }
                    $tabs[] = "$('#" . $id . '_' . $i . "')";
                    echo N2Html::openTag('div', array(
                        'id'       => $id . '_' . $i,
                        'style'    => 'display:' . $display . ';',
                        'data-tab' => N2XmlHelper::getAttribute($tab->_xml, 'name')
                    ));
                    $tab->render($control_name);
                    echo N2Html::closeTag('div');
                    $i++;
                }
                ?>
            </div>
        </div>
        <script type="text/javascript">
            nextend.ready(
                function ($) {
                    new NextendHeadingPane($('#<?php echo $id; ?>'), $('#<?php echo $id; ?> > .n2-labels .n2-td'), [
                        <?php echo implode(',', $tabs); ?>
                    ]);
                }
            );
        </script>
        <?php
    }

    function getLabel($tab, $underlined = false) {
        $icon = N2XmlHelper::getAttribute($tab->_xml, 'icon');
        if (!empty($icon)) {
            $attrs = array(
                'class' => 'n2-i ' . $icon
            );
            $tip   = N2XmlHelper::getAttribute($tab->_xml, 'tip');
            if (!empty($tip)) {
                $attrs['data-n2tip'] = n2_($tip);
            }
            return N2Html::tag('div', $attrs, '');
        }
        $class = ($underlined ? 'n2-underline' : '');
        return N2Html::tag('span', array(
            'class' => $class
        ), n2_(N2XmlHelper::getAttribute($tab->_xml, 'label')));
    }

}
