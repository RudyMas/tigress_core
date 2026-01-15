<?php

namespace Tigress;

use Controller\Menu;
use Exception;
use Twig\Error\LoaderError;

/**
 * Class Core (PHP version 8.5)
 *
 * Following constants are defined:
 * - TIGRESS_CORE_VERSION   Contains the version of the Tigress Core
 * - CONFIG                 Contains the config.json file
 * - SYSTEM                 Contains the system.json file
 * - SERVER_TYPE            Contains the type of server (development, test, production)
 * - DATABASE               Contains the database connections
 * - TWIG                   Contains the Twig instance
 * - ROUTER                 Contains the Router instance
 * - SECURITY               Contains the security class
 * - RIGHTS                 Contains the rights class
 * - WEBSITE                Contains information about the website
 * - MENU                   Contains the menu class
 * - BASE_URL               Path to the root of the website (URL)
 * - SYSTEM_ROOT            Full system path to the root of the website
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024-2026, rudymas.be. (http://www.rudymas.be/)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2026.01.15.0
 * @package Tigress\Core
 */
class Core
{
    /**
     * @throws LoaderError
     * @throws Exception
     */
    public function __construct()
    {
        define('TIGRESS_CORE_VERSION', '2026.01.15');

        // Load the config files
        if (file_exists('config/config.json') === true) {
            $config = json_decode(file_get_contents('config/config.json'));
            if (!empty($_SESSION['user']['locale']) === true) {
                $config->website->html_lang = $_SESSION['user']['locale'];
            }
            define('CONFIG', $config);
        }

        if (file_exists('system/config.json') === true) {
            $system = json_decode(file_get_contents('system/config.json'));
        }

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

        // Define the constants for the website information
        define('WEBSITE', CONFIG->website ?? '');

        // Set the timezone
        date_default_timezone_set($system->timezone);

        // Check if the database is enabled and connect to it
        if (CONFIG->packages->tigress_database === true) {
            if (!$this->connectDatabase()) throw new Exception('No database connection possible', 500);
        }

        // Create the translation helper instance
        define('TRANSLATIONS', new TranslationHelper());

        // Create a new Twig instance
        define('TWIG', new DisplayHelper($system->Core->Twig->views, $system->debug));
        TWIG->addPath(SYSTEM_ROOT . '/vendor/tigress/core/src/views');

        // Load the menu class
        define('MENU', new Menu());

        // Load the security class
        define('SECURITY', new Security());
        define('RIGHTS', new Rights());

        foreach (CONFIG->servers as $server => $type) {
            if (isset($_SERVER['HTTP_HOST'])) {
                if ($_SERVER['HTTP_HOST'] == $server || strpos($_SERVER['HTTP_HOST'], $server)) {
                    define('SERVER_TYPE', $type);
                    break;
                }
            } else {
                define('SERVER_TYPE', 'development');
            }
        }

        if (SERVER_TYPE === 'development') {
            $system->debug = true;
        }
        define('SYSTEM', $system);

        $router = new Router();
        define('ROUTER', $router);
        RIGHTS->setRights($router->createRoutes());
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
        $database = [];
        foreach (CONFIG->servers as $server => $type) {
            if (isset($_SERVER['HTTP_HOST'])) {
                if ($_SERVER['HTTP_HOST'] == $server || strpos($_SERVER['HTTP_HOST'], $server)) {
                    foreach (CONFIG->databases->$type as $key => $value) {
                        $database[$key] = new Database(
                            $value->host,
                            $value->port,
                            $value->username,
                            $value->password,
                            $value->database,
                            $value->charset,
                            $value->dbType,
                        );
                    }
                    define('DATABASE', $database);
                    return true;
                }
            } else {
                foreach (CONFIG->databases->development as $key => $value) {
                    $database[$key] = new Database(
                        $value->host,
                        $value->port,
                        $value->username,
                        $value->password,
                        $value->database,
                        $value->charset,
                        $value->dbType,
                    );
                }
                define('DATABASE', $database);
                return true;
            }
        }
        define('DATABASE', $database);
        return false;
    }

    /**
     * Creating BASE_URL & SYSTEM_ROOT
     *
     * BASE_URL = Path to the root of the website
     * SYSTEM_ROOT = Full system path to the root of the website
     */
    public static function settingUpRootMapping(): void
    {
        $arrayServerName = explode('.', $_SERVER['SERVER_NAME']);

        $numberOfServerNames = count($arrayServerName);
        unset($arrayServerName[$numberOfServerNames - 2]);
        unset($arrayServerName[$numberOfServerNames - 1]);

        $scriptName = rtrim(str_replace($arrayServerName, '', dirname($_SERVER['SCRIPT_NAME'])), '/\\');
        define('BASE_URL', $scriptName);

        $extraPath = '';
        if (defined('SYSTEM') && SYSTEM->subdomain_in_subfolder) {
            for ($i = 0; $i < count($arrayServerName); $i++) {
                $extraPath .= '/' . $arrayServerName[$i];
            }
        }
        define('SYSTEM_ROOT', $_SERVER['DOCUMENT_ROOT'] . $extraPath);
    }

    /**
     * @param mixed $data
     * @param bool $stop
     */
    public static function dump(mixed $data, bool $stop = true): void
    {
        print('<pre>');
        print_r($data);
        print('</pre>');
        if ($stop) exit;
    }
}
