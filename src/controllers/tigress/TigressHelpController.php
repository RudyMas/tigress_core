<?php

namespace Controller\tigress;

/**
 * Class TigressHelpController (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license Apache License 2.0 (https://www.apache.org/licenses/LICENSE-2.0)
 * @version 2024.11.28.0
 * @package Controller\tigress\TigressHelpController
 */
class TigressHelpController
{
    /**
     * @return void
     */
    public function index(): void
    {
        $output = <<<HTML
        <h1>Tigress Help</h1>
        <p>We will be adding a manual to the framework in the near future.</p>
HTML;
        print $output;
    }
}