<?php

namespace Tigress;

use Exception;
use stdClass;
use Twig\Error\LoaderError;

/**
 * Class Core (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024, rudymas.be. (http://www.rudymas.be/)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 0.1.3
 * @lastmodified 2024-07-03
 * @package Tigress
 */
class Core
{
    public TwigHelper $Twig;
    public Database $Database;
    public stdClass $Config;
    public stdClass $Routes;
    public stdClass $System;

    /**
     * @throws LoaderError
     * @throws Exception
     */
    public function __construct()
    {
        define('TIGRESS_CORE_VERSION', '0.1.3');

        // Check if the config files exist
        if (
            file_exists('config/config.json') === false
            || file_exists('config/routes.json') === false
            || file_exists('system/config.json') === false
        ) {
            FrameworkHelper::create();
        }

        // Load the config files
        $this->Config = json_decode(file_get_contents('config/config.json'));
        $this->Routes = json_decode(file_get_contents('config/routes.json'));
        $this->System = json_decode(file_get_contents('system/config.json'));

        // Create BASE_URL, SYSTEM_ROOT & others
        $this->settingUpRootMapping();

        // Check if the database is enabled & connect to it
        if ($this->Config->packages->tigress_database === true) {
            if(!$this->connectDatabase()) throw new Exception('No database connection possible', 500);
        }

        // Create a new Twig instance
        $this->Twig = new TwigHelper($this->System->Core->Twig->views, $this->System->Core->Twig->debug);
        $this->Twig->addPath('vendor/tigress/core/src/views');

        $Router = new Router($this);
    }

    /**
     * Connect to the database
     *
     * @return bool
     * @throws Exception
     */
    private function connectDatabase(): bool
    {
        foreach ($this->Config->servers as $server => $type) {
            if (isset($_SERVER['HTTP_HOST'])) {
                if (strpos($_SERVER['HTTP_HOST'], $server)) {
                    $this->Database = new Database(
                        $this->Config->databases->$type->host,
                        $this->Config->databases->$type->port,
                        $this->Config->databases->$type->username,
                        $this->Config->databases->$type->password,
                        $this->Config->databases->$type->database,
                        $this->Config->databases->$type->charset,
                        $this->Config->databases->$type->dbType,
                    );
                    return true;
                }
            } else {
                $this->Database = new Database(
                    $this->Config->databases->development->host,
                    $this->Config->databases->development->port,
                    $this->Config->databases->development->username,
                    $this->Config->databases->development->password,
                    $this->Config->databases->development->database,
                    $this->Config->databases->development->charset,
                    $this->Config->databases->development->dbType,
                );
                return true;
            }
        }
        return false;
    }

    /**
     * Creating BASE_URL & SYSTEM_ROOT
     *
     * BASE_URL = Path to the root of the website
     * SYSTEM_ROOT = Full system path to the root of the website
     */
    private function settingUpRootMapping(): void
    {
        $arrayServerName = explode('.', $_SERVER['SERVER_NAME']);
        $numberOfServerNames = count($arrayServerName);
        unset($arrayServerName[$numberOfServerNames-2]);
        unset($arrayServerName[$numberOfServerNames-1]);

        $scriptName = rtrim(str_replace($arrayServerName, '', dirname($_SERVER['SCRIPT_NAME'])), '/\\');
        define('BASE_URL', $scriptName);

        $extraPath = '';
        for ($i = 0; $i < count($arrayServerName); $i++) {
            $extraPath .= '/' . $arrayServerName[$i];
        }
        define('SYSTEM_ROOT', $_SERVER['DOCUMENT_ROOT'] . $extraPath . BASE_URL);

        define('WEBSITE', $this->Config->website ?? '');
    }
}