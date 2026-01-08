<?php

namespace Controller\Core;

use Random\RandomException;
use Repository\SystemSettingsRepo;
use Tigress\EncryptionAES;
use Tigress\EncryptionRSA;
use Tigress\Repository;

/**
 * Class SettingsController (PHP version 8.4)
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
 * @copyright 2024-2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.01.14.0
 * @package Controller\Core\SettingsController
 */
class SettingsController
{
    private bool $enableEncryption;
    private EncryptionRSA $encryption;
    private EncryptionAES $largeEncryption;
    private string $publicKey;
    private string $privateKey;
    private int $keySize = 2048;
    private int $maxRsaPayloadSize;
    private Repository $systemSettings;

    /**
     * Returns the version of the SettingsController
     *
     * @return string
     */
    public static function version(): string
    {
        return '2025.01.14';
    }

    /**
     * SettingsController constructor.
     */
    public function __construct(bool $encryption = false)
    {
        $this->systemSettings = new SystemSettingsRepo();

        $this->enableEncryption = $encryption;
        if ($encryption) {
            $this->encryption = new EncryptionRSA();
            $this->maxRsaPayloadSize = ($this->keySize / 8) -2 -2 * 32;
            $this->largeEncryption = new EncryptionAES();
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
                // Check if $setting->value is encrypted with AES
                if (str_contains($setting->value, ':aes:')) {
                    $parts = explode(':aes:', $setting->value);
                    $symmetricKey = $this->encryption->decrypt($parts[0]);
                    $iv = $parts[1];
                    $encryptedData = $parts[2];

                    $this->largeEncryption->setKey($symmetricKey);
                    $this->largeEncryption->setIv($iv);

                    $data[$setting->setting] = $this->largeEncryption->decrypt($encryptedData);
                } else {
                    $data[$setting->setting] = $this->encryption->decrypt($setting->value);
                }
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
     * @throws RandomException
     */
    public function saveSettings(array $settings): void
    {
        if ($this->enableEncryption) {
            $this->encryption->setKey(file_get_contents(SYSTEM_ROOT . $this->publicKey));
            foreach ($settings as $key => $value) {
                $this->systemSettings->new();
                $setting = $this->systemSettings->current();
                $setting->setting = $key;

                if (strlen($value) <= $this->maxRsaPayloadSize) {
                    $setting->value = $this->rsaEncrypt($value);
                } else {
                    $setting->value = $this->aesEncrypt($value);
                }

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
     * Get the public key
     *
     * @param string $privateKey
     * @return void
     */
    public function setPrivateKey(string $privateKey): void
    {
        $this->privateKey = $privateKey;
    }

    /**
     * Get the RSA key size
     *
     * @return int
     */
    public function getKeySize(): int
    {
        return $this->keySize;
    }

    /**
     * Set the RSA key size
     *
     * @param int $keySize
     * @return void
     */
    public function setKeySize(int $keySize): void
    {
        $this->keySize = $keySize;
    }

    /**
     * @param mixed $value
     * @return string
     */
    private function rsaEncrypt(mixed $value): string
    {
        return $this->encryption->encrypt($value);
    }

    /**
     * @param mixed $value
     * @return string
     * @throws RandomException
     */
    private function aesEncrypt(mixed $value): string
    {
        // Encrypt with AES and RSA for the AES key
        $symmetricKey = $this->largeEncryption->createKey();
        $this->largeEncryption->setKey($symmetricKey);

        $iv = $this->largeEncryption->createIv();
        $this->largeEncryption->setIv($iv);

        $encryptedData = $this->largeEncryption->encrypt($value);

        $encryptedSymmetricKey = $this->encryption->encrypt($symmetricKey);

        return $encryptedSymmetricKey . ':aes:' . $iv . ':aes:' . $encryptedData;
    }
}