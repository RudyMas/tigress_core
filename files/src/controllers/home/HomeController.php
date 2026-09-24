<?php

namespace Controller\home;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class HomeController (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/Apache-2.0 License Apache-2.0
 * @version 2025.02.28.0
 * @package Controller\HomeController
 */
class HomeController
{
    /**
     * Home page
     *
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function index(): void
    {
        TWIG->render('home/home.twig');
    }
}