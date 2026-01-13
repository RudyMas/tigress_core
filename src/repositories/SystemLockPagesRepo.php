<?php

namespace Repository;

use Tigress\Repository;

/**
 * Repository for the system_lock_pages table
 */
class SystemLockPagesRepo extends Repository
{
    public function __construct()
    {
        $this->dbName = 'default';
        $this->table = 'system_lock_pages';
        $this->primaryKey = ['resource', 'resource_id'];
        $this->model = 'DefaultModel';
        $this->autoload = true;

        $this->createTable = [
            'table' => "
                CREATE TABLE `{$this->table}` (
                  `resource` varchar(50) NOT NULL,
                  `resource_id` varchar(11) NOT NULL,
                  `locked_by_user_id` int(11) NOT NULL,
                  `locked_at` timestamp NOT NULL,
                  `expires_at` timestamp NOT NULL
                ) ENGINE=InnoDB
                  DEFAULT CHARSET=utf8mb4
                  COLLATE=utf8mb4_general_ci
                  ROW_FORMAT=DYNAMIC;
            ",
            'indexes' => [
                "ALTER TABLE `{$this->table}` ADD PRIMARY KEY (`resource`, `resource_id`);"
            ],
            'seed' => []
        ];

        parent::__construct();
    }
}
