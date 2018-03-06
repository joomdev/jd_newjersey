<?php

N2Loader::import('libraries.form.element.list');

class N2ElementMenuitems extends N2ElementList
{

    function fetchElement() {
		$menu = JMenu::getInstance('site');
		$menuItems = $menu->getItems($attributes = array(), $values = array());		

        $this->_xml->addChild('option', htmlspecialchars(n2_('Default')))
                   ->addAttribute('value', 0);				   
				   
        if (count($menuItems)) {
            foreach ($menuItems AS $item) {
                $this->_xml->addChild('option', htmlspecialchars('['. $item->id . '] '.$item->title))
                           ->addAttribute('value', $item->id);
            }
        }
        return parent::fetchElement();
    }

}