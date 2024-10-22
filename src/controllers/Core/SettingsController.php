<?php

namespace Controller\Core;

use Repository\system_settings_repo;
use Tigress\Repository;

/**
 * Class SettingsController - This class is used in combination with the system_settings table (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 0.1.0
 * @package Controller\Core\SettingsController
 */
class SettingsController
{
    private Repository $systemSettings;

    public function __construct()
    {
        $this->systemSettings = new system_settings_repo();
    }

    public function loadSettings(string $settings): void
    {
        $sql = "SELECT *
                FROM system_settings
                WHERE settings IN ({$settings})";
        $this->systemSettings->loadByQuery($sql);
        print('<pre>');
        print_r($this->systemSettings);
        print('</pre>');
    }
}