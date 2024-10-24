<?php

namespace Controller\Core;

use Repository\system_settings_repo;
use Tigress\EncryptionRSA;
use Tigress\Repository;

/**
 * Class SettingsController - This class is used in combination with the system_settings table (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 0.6.0
 * @lastmodified 2024-10-24
 * @package Controller\Core\SettingsController
 */
class SettingsController
{
    private EncryptionRSA $encryption;
    private Repository $systemSettings;

    /**
     * Returns the version of the SettingsController
     *
     * @return string
     */
    public static function version(): string
    {
        return '0.6.0';
    }

    /**
     * SettingsController constructor.
     */
    public function __construct(bool $encryption = false)
    {
        $this->systemSettings = new system_settings_repo();

        if ($encryption) {
            $this->encryption = new EncryptionRSA();
        }
    }

    /**
     * Load the settings from the database
     *
     * @param array $settings
     * @return void
     */
    public function loadSettings(array $settings): void
    {
        $where = '';
        foreach ($settings as $key => $value) {
            $where .= "'{$key}', ";
        }
        $where = rtrim($where, ', ');

        $sql = "SELECT *
                FROM system_settings
                WHERE setting IN ({$where})";
        $this->systemSettings->loadByQuery($sql);
    }

    /**
     * Get the settings from the database
     *
     * @return array
     */
    public function getSettings(): array
    {
        $this->encryption->setKey(file_get_contents(SYSTEM_ROOT . '/private/keys/private.pem'));
        $data = [];
        foreach ($this->systemSettings as $setting) {
            $data[$setting->setting] = $this->encryption->decrypt($setting->value);
        }
        return $data;
    }

    /**
     * Save the settings to the database
     *
     * @param array $settings
     * @return void
     */
    public function saveSettings(array $settings): void
    {
        $this->encryption->setKey(file_get_contents(SYSTEM_ROOT . '/private/keys/public.pem'));
        foreach ($settings as $key => $value) {
            $this->systemSettings->new();
            $setting = $this->systemSettings->current();
            $setting->setting = $key;
            $setting->value = $this->encryption->encrypt($value);
            $this->systemSettings->save($setting);
        }
    }
}