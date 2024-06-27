<?php

namespace Tigress;

use stdClass;

/**
 * Class Core (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024, rudymas.be. (http://www.rudymas.be/)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 0.0.3
 * @package Tigress
 */
class Core
{
    public TwigHelper $Twig;
    public stdClass $Config;
    public stdClass $System;

    public function __construct()
    {
        define('TIGRESS_CORE_VERSION', '0.0.3');

        if (
            file_exists('config/config.json') === false
            || file_exists('system/config.json') === false
        ) {
            FrameworkHelper::create();
        }

        $this->Config = json_decode(file_get_contents('config/config.json'));
        $this->System = json_decode(file_get_contents('system/config.json'));

        $this->Twig = new TwigHelper($this->System->Core->Twig->views, $this->System->Core->Twig->debug);
    }
}