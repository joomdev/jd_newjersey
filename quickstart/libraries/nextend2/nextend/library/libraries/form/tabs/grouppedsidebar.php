<?php
N2Loader::import('libraries.form.tab');
N2Loader::import('libraries.form.tabs.tabbedsidebar');

class N2TabGrouppedSidebar extends N2TabTabbedSidebar
{

    var $_tabs;

    function render($control_name) {
        $this->initTabs();
        foreach ($this->_tabs AS $tabname => $tab) {
            $tab->render($control_name);
        }
    }
}