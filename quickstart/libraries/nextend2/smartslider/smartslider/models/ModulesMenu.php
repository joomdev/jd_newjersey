<?php

class N2SmartsliderModulesMenuModel extends NextendModel
{

    public function __construct() {
        parent::__construct();

        $this->db->setTableName("modules_menu");
    }

} 