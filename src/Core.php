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
 * @version 0.3.2
 * @lastmodified 2024-07-05
 * @package Tigress\Core
 */
class Core
{
    public DisplayHelper $Twig;
    public array $Database = [];
    public stdClass $Config;
    public stdClass $Routes;
    public stdClass $System;

    /**
     * @throws LoaderError
     * @throws Exception
     */
    public function __construct()
    {
        define('TIGRESS_CORE_VERSION', 'v0.3.2');

        // Create BASE_URL, SYSTEM_ROOT & others
        $this->settingUpRootMapping();

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

        // Define the constants for the website information
        define('WEBSITE', $this->Config->website ?? '');

        // Check if the database is enabled & connect to it
        if ($this->Config->packages->tigress_database === true) {
            if(!$this->connectDatabase()) throw new Exception('No database connection possible', 500);
        }

        // Create a new Twig instance
        $this->Twig = new DisplayHelper($this->System->Core->Twig->views, $this->System->Core->debug);
        $this->Twig->addPath('vendor/tigress/core/src/views');

        $router = new Router($this);
        $router->execute();
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
                    foreach($this->Config->databases->$type as $key => $value) {
                        $this->Database[$key] = new Database(
                            $value->host,
                            $value->port,
                            $value->username,
                            $value->password,
                            $value->database,
                            $value->charset,
                            $value->dbType,
                        );
                    }
                    return true;
                }
            } else {
                foreach ($this->Config->databases->development as $key => $value) {
                    $this->Database[$key] = new Database(
                        $value->host,
                        $value->port,
                        $value->username,
                        $value->password,
                        $value->database,
                        $value->charset,
                        $value->dbType,
                    );
                }
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
    }

    /**
     * @param mixed $array
     * @param bool $stop
     */
    public static function dump($array, bool $stop = false): void
    {
        print('<pre>');
        print_r($array);
        print('</pre>');
        if ($stop) exit;
    }
}