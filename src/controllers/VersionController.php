<?php

namespace Controller;

use Tigress\Core;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class VersionController (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 0.9.0
 * @lastmodified 2024-09-03
 * @package Controller
 */
class VersionController
{
    private Core $Core;

    public function __construct(Core $Core)
    {
        $this->Core = $Core;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function index($args = []): void
    {
        $this->Core->Twig->render('version/index.twig', [
            'tigress_core_version' => TIGRESS_CORE_VERSION,
            'tigress_router_version' => TIGRESS_ROUTER_VERSION,
            'tigress_database_version' => TIGRESS_DATABASE_VERSION,
        ]);
    }
}