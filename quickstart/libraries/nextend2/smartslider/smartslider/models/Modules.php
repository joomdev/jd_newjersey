<?php

class N2SmartsliderModulesModel extends NextendModel
{

    public function __construct() {
        parent::__construct();

        $this->db->setTableName("modules");
    }


}