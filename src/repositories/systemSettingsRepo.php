<?php

namespace Repository;

use Tigress\Repository;

/**
 * Repository for system_settings table
 */
class systemSettingsRepo extends Repository
{
    public function __construct()
    {
        $this->dbName = 'default';
        $this->table = 'system_settings';
        $this->primaryKey = ['setting'];
        $this->model = 'systemSetting';
        $this->autoload = true;
        parent::__construct();
    }
}