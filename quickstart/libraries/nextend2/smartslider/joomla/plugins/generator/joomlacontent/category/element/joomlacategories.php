<?php

N2Loader::import('libraries.form.element.list');

class N2ElementJoomlaCategories extends N2ElementList
{

    function fetchElement() {

        $db = JFactory::getDBO();

        $query = 'SELECT
                    m.id, 
                    m.title AS name, 
                    m.title, 
                    m.parent_id AS parent, 
                    m.parent_id
                FROM #__categories m
                WHERE m.published = 1 AND (m.extension = "com_content" OR m.extension = "system")
                ORDER BY m.lft';


        $db->setQuery($query);
        $menuItems = $db->loadObjectList();
        $children  = array();
        if ($menuItems) {
            foreach ($menuItems as $v) {
                $pt   = $v->parent_id;
                $list = isset($children[$pt]) ? $children[$pt] : array();
                array_push($list, $v);
                $children[$pt] = $list;
            }
        }

        jimport('joomla.html.html.menu');
        $options = JHTML::_('menu.treerecurse', 1, '', array(), $children, 9999, 0, 0);
        if (count($options)) {
            foreach ($options AS $option) {
                if ($this->getValue() == '') {
                    $this->setValue($option->id);
                }
                $this->_xml->addChild('option', htmlspecialchars($option->treename))
                           ->addAttribute('value', $option->id);
            }
        }
        return parent::fetchElement();
    }

}
