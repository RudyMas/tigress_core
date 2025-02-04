<?php

namespace Controller\Core;

use JetBrains\PhpStorm\NoReturn;
use Tigress\Core;
use Tigress\EncryptionRSA;

/**
 * Class GoogleDriveController
 * - This class is used to load google documents by a link (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.02.04.0
 * @package Controller
 */
class GoogleDriveController
{
    private string $publicKey = '/private/keys/OLSC_public.key';
    private string $privateKey = '/private/keys/OLSC_private.key';

    /**
     * @var Core
     */
    private Core $Core;

    /**
     * Get the version of the ApiController
     *
     * @return string
     */
    public static function version(): string
    {
        return '2025.02.04';
    }

    /**
     * Show the Google document by a link
     * - This function is used to load google documents by a link
     * - encrypt the link if it is a Google Docs/Drive link
     *
     * @return void
     */
    #[NoReturn] public function loadByLink(): void
    {
        SECURITY->checkAccess();

        $repository = $_GET['repository'];
        $id = $_GET['id'];
        $field = $_GET['field'];

        $loadRepository = '\\Repository\\' . $repository;
        $repositoryClass = new $loadRepository();
        $repositoryClass->loadById($id);
        $repoData = $repositoryClass->current();
        $link = $repoData->$field;

        $encryption = new EncryptionRSA();
        if (preg_match('/^https?:\/\/(docs|drive).google.com\//', $link)) {
            $encryption->setKey(file_get_contents(SYSTEM_ROOT . $this->publicKey));
            $urlEncrypt = $encryption->encrypt($link);
            if ($repoData->isset($field)) {
                $repoData->$field = $urlEncrypt;
                $repositoryClass->save($repoData);
            }
            $show = $link;
        } else {
            $encryption->setKey(file_get_contents(SYSTEM_ROOT . $this->privateKey));
            $show = $encryption->decrypt($link);
        }

        TWIG->redirect($show);
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