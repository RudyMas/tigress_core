<?php

namespace Tigress;

use stdClass;
use Twig\Error\LoaderError;

/**
 * Class Core (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024, rudymas.be. (http://www.rudymas.be/)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 0.0.6
 * @package Tigress
 */
class Core
{
    public TwigHelper $Twig;
    public stdClass $Config;
    public stdClass $Routes;
    public stdClass $System;

    /**
     * @throws LoaderError
     */
    public function __construct()
    {
        define('TIGRESS_CORE_VERSION', '0.0.6');

        $this->settingUpRootMapping();

        if (
            file_exists('config/config.json') === false
            || file_exists('config/routes.json') === false
            || file_exists('system/config.json') === false
        ) {
            FrameworkHelper::create();
        }

        $this->Config = json_decode(file_get_contents('config/config.json'));
        $this->Routes = json_decode(file_get_contents('config/routes.json'));
        $this->System = json_decode(file_get_contents('system/config.json'));

        $this->Twig = new TwigHelper($this->System->Core->Twig->views, $this->System->Core->Twig->debug);
        $this->Twig->addPath('vendor/tigress/core/src/views');

        $Router = new Router($this);
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
}