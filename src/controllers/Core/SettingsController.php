<?php

namespace Controller\Core;

use Repository\system_settings_repo;
use Tigress\EncryptionRSA;
use Tigress\Repository;

/**
 * Class SettingsController (PHP version 8.3)
 *
 * This class is used in combination with the system_settings table.
 *     CREATE TABLE `system_settings` (
 *     `setting` varchar(30) NOT NULL,
 *     `value` text NOT NULL
 *     ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 *
 * It allows you to load, save and get settings from the database.
 * If encryption is enabled, it will encrypt the settings before saving them to the database.
 * If encryption is enabled, it will decrypt the settings before returning them.
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 0.6.5
 * @lastmodified 2024-10-25
 * @package Controller\Core\SettingsController
 */
class SettingsController
{
    private bool $enableEncryption;
    private EncryptionRSA $encryption;
    private string $publicKey;
    private string $privateKey;
    private Repository $systemSettings;

    /**
     * Returns the version of the SettingsController
     *
     * @return string
     */
    public static function version(): string
    {
        return '0.6.5';
    }

    /**
     * SettingsController constructor.
     */
    public function __construct(bool $encryption = false)
    {
        $this->systemSettings = new system_settings_repo();

        $this->enableEncryption = $encryption;
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
        $data = [];
        if ($this->enableEncryption) {
            $this->encryption->setKey(file_get_contents(SYSTEM_ROOT . $this->privateKey));
            foreach ($this->systemSettings as $setting) {
                $data[$setting->setting] = $this->encryption->decrypt($setting->value);
            }
        } else {
            foreach ($this->systemSettings as $setting) {
                $data[$setting->setting] = $setting->value;
            }
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
        if ($this->enableEncryption) {
            $this->encryption->setKey(file_get_contents(SYSTEM_ROOT . $this->publicKey));
            foreach ($settings as $key => $value) {
                $this->systemSettings->new();
                $setting = $this->systemSettings->current();
                $setting->setting = $key;
                $setting->value = $this->encryption->encrypt($value);
                $this->systemSettings->save($setting);
            }
        } else {
            foreach ($settings as $key => $value) {
                $this->systemSettings->new();
                $setting = $this->systemSettings->current();
                $setting->setting = $key;
                $setting->value = $value;
                $this->systemSettings->save($setting);
            }
        }
    }

    /**
     * Set the public key
     *
     * @param string $publicKey
     * @return void
     */
    public function setPublicKey(string $publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    /**
     * Set the private key
     *
     * @param string $privateKey
     * @return void
     */
    public function setPrivateKey(string $privateKey): void
    {
        $this->privateKey = $privateKey;
    }
}