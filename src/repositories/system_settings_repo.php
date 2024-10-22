<?php

namespace Repository;

use Tigress\Repository;

/**
 * Repository for system_settings table
 */
class system_settings_repo extends Repository
{
    public function __construct()
    {
        $this->dbName = 'default';
        $this->table = 'system_settings';
        $this->primaryKey = ['setting'];
        $this->model = 'system_setting';
        $this->autoload = true;
        parent::__construct();
    }
}