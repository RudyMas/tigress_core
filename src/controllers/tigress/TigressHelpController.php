<?php

namespace Controller\tigress;

/**
 * Class TigressHelpController (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 0.1.0
 * @lastmodified 2024-10-03
 * @package Controller\tigress_help\TigressHelpController
 */
class TigressHelpController
{
    public function index()
    {
        $output = <<<HTML
        <h1>Tigress Help</h1>
        <p>We will be adding a manual to the framework in the near future.</p>
HTML;
        print $output;
    }
}