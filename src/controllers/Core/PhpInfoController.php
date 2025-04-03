<?php

namespace Controller\Core;

use Random\RandomException;
use Repository\systemSettingsRepo;
use Tigress\EncryptionAES;
use Tigress\EncryptionRSA;
use Tigress\Repository;

/**
 * Class PhpInfoController (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.04.03.0
 * @package Controller\Core\PhpInfoController
 */
class PhpInfoController
{
    /**
     * Returns the version of the SettingsController
     *
     * @return string
     */
    public static function version(): string
    {
        return '2025.04.03';
    }

    /**
     * Displays the PHP information
     *
     * @return void
     * @throws RandomException
     */
    public function index(): void
    {
        // Check if the user has the right to access this page
        if (RIGHTS->checkRights() == false) {
            $_SESSION['error'] = 'You do not have the right to access the PhpInfo page.';
            TWIG->redirect('/home');
        }
        phpinfo();
    }
}