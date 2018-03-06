<?php
N2Loader::import('libraries.form.element');

/**
 * Class N2Tab
 */
class N2Tab {

    /**
     * @var
     */
    var $_form;

    /**
     * @var
     */
    var $_xml;

    /**
     * @var string
     */
    var $_name;

    /**
     * @var
     */
    var $_attributes;

    /**
     * @var
     */
    var $_elements;

    var $_hide = false;

    public $_tabs;

    /**
     * @param $form
     * @param $xml
     */
    public function __construct(&$form, &$xml) {
        $this->_form      = $form;
        $this->_xml       = $xml;
        $this->_name      = N2XmlHelper::getAttribute($xml, 'name');
        $this->_hidetitle = N2XmlHelper::getAttribute($xml, 'hidetitle');
        $this->initElements();
    }

    function initElements() {
        $this->_elements = array();
        foreach ($this->_xml->param AS $element) {
            $test = N2XmlHelper::getAttribute($element, 'test');
            if ($this->_form->makeTest($test)) {

                $class = N2Form::importElement(N2XmlHelper::getAttribute($element, 'type'));
                if (!class_exists($class, false)) {
                    throw new Exception($class . ' missing in ' . $this->_form->_xmlfile);
                }

                $field = new $class($this->_form, $this, $element);
                if ($field->_name) {
                    $this->_elements[$field->_name] = $field;
                } else {
                    $this->_elements[] = $field;
                }
            }
        }
    }

    /**
     * @param $control_name
     */
    function render($control_name) {

        ob_start();
        $this->decorateTitle();
        $this->decorateGroupStart();
        $keys = array_keys($this->_elements);
        for ($i = 0; $i < count($keys); $i++) {
            $this->decorateElement($this->_elements[$keys[$i]], $this->_elements[$keys[$i]]->render($control_name), $i);
        }
        $this->decorateGroupEnd();

        if ($this->_hide) {
            echo N2Html::tag('div', array('style' => 'display: none;'), ob_get_clean());
        } else {
            echo ob_get_clean();
        }

    }

    function decorateTitle() {
        echo "<div id='n2-tab-" . N2XmlHelper::getAttribute($this->_xml, 'name') . "' class='n2-form-tab " . N2XmlHelper::getAttribute($this->_xml, 'class') . "'>";
        $this->_renderTitle();
    }

    protected function _renderTitle() {
        if ($this->_hidetitle != 1) {
            echo N2Html::tag('div', array(
                'class' => 'n2-h2 n2-content-box-title-bg'
            ), n2_(N2XmlHelper::getAttribute($this->_xml, 'label')));
        }
    }

    function decorateGroupStart() {
        echo "<table>";
        echo N2Html::tag('colgroup', array(), N2Html::tag('col', array('class' => 'n2-label-col'), '', false) . N2Html::tag('col', array('class' => 'n2-element-col'), '', false));
    }

    function decorateGroupEnd() {
        echo "</table>";
        echo "</div>";
    }

    /**
     * @param $el
     * @param $out
     * @param $i
     */
    function decorateElement(&$el, $out, $i) {
        $attrs = array();
        if (isset($el->_xml->attribute)) {
            foreach ($el->_xml->attribute AS $attr) {
                $attrs[N2XmlHelper::getAttribute($attr, 'type')] = (string)$attr;
            }
        }
        echo N2Html::openTag('tr', $attrs + array('class' => N2XmlHelper::getAttribute($el->_xml, 'class')));
        $colSpan = '';
        if ($out[0] != '') {
            echo "<td class='n2-label" . ($el->hasLabel ? '' : ' n2-empty-label') . "'>" . $out[0] . "</td>";
        } else {
            $colSpan = 'colspan="2"';
        }
        echo "<td class='n2-element' {$colSpan}>" . $out[1] . "</td>";
        echo "</tr>";
    }

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

            N2Pluggable::doAction('N2TabTabbed' . N2XmlHelper::getAttribute($this->_xml, 'name'), array(
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

                $a                                          = array();
                $a[N2XmlHelper::getAttribute($tab, 'name')] = new $class($this->_form, $tab);
                $this->_tabs                                = self::array_insert($this->_tabs, $a, $position);
            }
        }
    }

    public function removeTab($name) {
        if (isset($this->_tabs[$name])) {
            unset($this->_tabs[$name]);
        }
    }

    private function array_insert($array, $values, $offset) {
        return array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset, NULL, true);
    }
}

class N2TabDark extends N2Tab {

    function decorateTitle() {
        echo "<div id='n2-tab-" . N2XmlHelper::getAttribute($this->_xml, 'name') . "' class='n2-form-tab " . N2XmlHelper::getAttribute($this->_xml, 'class') . "'>";
        if ($this->_hidetitle != 1) {
            echo N2Html::tag('div', array(
                'class' => 'n2-h3 n2-sidebar-header-bg n2-uc'
            ), n2_(N2XmlHelper::getAttribute($this->_xml, 'label')));
        }
    }
}
