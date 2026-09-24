<?php

namespace Repository;

use Tigress\Repository;

/**
 * Repository for system_settings table
 */
class SystemSettingsRepo extends Repository
{
    public function __construct()
    {
        $this->dbName = 'default';
        $this->table = 'system_settings';
        $this->primaryKey = ['setting'];
        $this->model = 'DefaultModel';
        $this->autoload = true;
        $this->createTable = [
            'table' => "
                CREATE TABLE `{$this->table}` (
                  `setting` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
                  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            ",
            'indexes' => [
                "ALTER TABLE `{$this->table}` ADD PRIMARY KEY (`setting`);"
            ],
            'seed' => []
        ];
        parent::__construct();
    }
}