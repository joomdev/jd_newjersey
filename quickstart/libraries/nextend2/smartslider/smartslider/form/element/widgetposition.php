<?php

N2Loader::import('libraries.form.element.group');

class N2ElementWidgetPosition extends N2ElementGroup {

    function fetchElement() {
        $values = explode('|*|', $this->getValue());
        if (!isset($values[6]) || $values[6] == '') {
            $values[6] = 1;
        }
        $values[6] = intval($values[6]);
        $this->_form->set($this->_name, implode('|*|', $values));

        $mode = $this->_xml->addChild('param');
        $mode->addAttribute('name', $this->_name . '-mode');
        $mode->addAttribute('type', 'switcher');
        $mode->addAttribute('label', 'Mode');
        $mode->addAttribute('default', 'simple');
        $mode->addAttribute('translateable', '1');
        $mode->addAttribute('class', 'n2-expert');
        $mode->addAttribute('post', 'break');

        $simple = $mode->addChild('unit', 'Simple');
        $simple->addAttribute('value', 'simple');
        $advanced = $mode->addChild('unit', 'Advanced');
        $advanced->addAttribute('value', 'advanced');

        $this->addSimple();

        $this->addAdvanced();

        N2JS::addInline('new N2Classes.FormElementWidgetPosition("' . $this->_id . '");');

        return parent::fetchElement();
    }

    protected function addSimple() {

        $simple = $this->_xml->addChild('param');
        $simple->addAttribute('type', 'group');

        $area = $simple->addChild('param');
        $area->addAttribute('type', 'sliderwidgetarea');
        $area->addAttribute('name', $this->_name . '-area');
        $area->addAttribute('default', N2XmlHelper::getAttribute($this->_xml, 'area'));

        $priority = $simple->addChild('param');
        $priority->addAttribute('type', 'list');
        $priority->addAttribute('name', $this->_name . '-stack');
        $priority->addAttribute('label', n2_('Stack'));
        $priority->addAttribute('default', N2XmlHelper::getAttribute($this->_xml, 'stack', '1'));

        for ($i = 1; $i < 5; $i++) {
            $pri = $priority->addChild('option', $i);
            $pri->addAttribute('value', $i);
        }

        $offset = $simple->addChild('param');
        $offset->addAttribute('type', 'text');
        $offset->addAttribute('name', $this->_name . '-offset');
        $offset->addAttribute('label', 'Offset');
        $offset->addAttribute('style', 'width:30px;');
        $offset->addAttribute('default', N2XmlHelper::getAttribute($this->_xml, 'offset', '0'));

        $offset->addChild('unit', 'px')
               ->addAttribute('value', 'px');
    }

    protected function addAdvanced() {

        $advanced = $this->_xml->addChild('param');
        $advanced->addAttribute('type', 'group');
        $advanced->addAttribute('style', 'width:350px;');

        $horizontal = $advanced->addChild('param');
        $horizontal->addAttribute('name', $this->_name . '-horizontal');
        $horizontal->addAttribute('type', 'switcher');
        $horizontal->addAttribute('label', 'Horizontal');
        $horizontal->addAttribute('default', 'left');
        $horizontal->addAttribute('translateable', '1');

        $left = $horizontal->addChild('unit', 'Left');
        $left->addAttribute('value', 'left');
        $right = $horizontal->addChild('unit', 'Right');
        $right->addAttribute('value', 'right');

        $position = $advanced->addChild('param');
        $position->addAttribute('name', $this->_name . '-horizontal-position');
        $position->addAttribute('type', 'text');
        $position->addAttribute('label', n2_x('Position', "position for controls"));
        $position->addAttribute('default', '0');
        $position->addAttribute('style', 'width:30px;');

        $switcher = $advanced->addChild('param');
        $switcher->addAttribute('name', $this->_name . '-horizontal-unit');
        $switcher->addAttribute('type', 'switcher');
        $switcher->addAttribute('label', n2_('Unit'));
        $switcher->addAttribute('default', 'px');

        $px = $switcher->addChild('unit', 'px');
        $px->addAttribute('value', 'px');
        $percent = $switcher->addChild('unit', n2_('%'));
        $percent->addAttribute('value', '%');


        $vertical = $advanced->addChild('param');
        $vertical->addAttribute('name', $this->_name . '-vertical');
        $vertical->addAttribute('type', 'switcher');
        $vertical->addAttribute('label', 'Vertical');
        $vertical->addAttribute('default', 'top');
        $vertical->addAttribute('translateable', '1');

        $left = $vertical->addChild('unit', 'Top');
        $left->addAttribute('value', 'top');
        $right = $vertical->addChild('unit', 'Bottom');
        $right->addAttribute('value', 'bottom');

        $position = $advanced->addChild('param');
        $position->addAttribute('name', $this->_name . '-vertical-position');
        $position->addAttribute('type', 'text');
        $position->addAttribute('label', n2_x('Position', "position for controls"));
        $position->addAttribute('default', '0');
        $position->addAttribute('style', 'width:30px;');

        $switcher = $advanced->addChild('param');
        $switcher->addAttribute('name', $this->_name . '-vertical-unit');
        $switcher->addAttribute('type', 'switcher');
        $switcher->addAttribute('label', n2_('Unit'));
        $switcher->addAttribute('default', 'px');

        $px = $switcher->addChild('unit', 'px');
        $px->addAttribute('value', 'px');
        $percent = $switcher->addChild('unit', n2_('%'));
        $percent->addAttribute('value', '%');
    }
}
