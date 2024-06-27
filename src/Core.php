<?php

namespace Tigress;

/**
 * Class Core (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024, rudymas.be. (http://www.rudymas.be/)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 0.0.1
 * @package Tigress
 */
class Core
{
    public TwigHelper $twig;

    public function __construct(string $twigViewFolder, bool $twigDebug = false)
    {
        define('TIGRESS_CORE_VERSION', '0.0.1');

        $config = file_get_contents('config/config.json');

        $this->twig = new TwigHelper($twigViewFolder, $twigDebug);
    }
}