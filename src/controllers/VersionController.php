<?php

namespace Controller;

use Exception;
use Tigress\Core;
use Tigress\Database;
use Tigress\DataConverter;
use Tigress\DisplayHelper;
use Tigress\FileManager;
use Tigress\FrameworkHelper;
use Tigress\PdfCreatorHelper;
use Tigress\Router;
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
 * @package Controller\VersionController
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
     * @throws LoaderError|Exception
     */
    public function index($args = []): void
    {
        $framework_helper_version = class_exists('Tigress\FrameworkHelper') ? FrameworkHelper::version() : '-';
        $display_helper_version = class_exists('Tigress\DisplayHelper') ? DisplayHelper::version() : '-';
        $pdf_creator_helper_version = class_exists('Tigress\PdfCreatorHelper') ? PdfCreatorHelper::version() : '-';

        $tigress_router_version = class_exists('Tigress\Router') ? Router::version() : '-';
        $tigress_database_version = class_exists('Tigress\Database') ? Database::version() : '-';
        $tigress_data_converter_version = class_exists('Tigress\DataConverter') ? DataConverter::version() : '-';
        $tigress_file_manager_version = class_exists('Tigress\FileManager') ? FileManager::version() : '-';

        $this->Core->Twig->render('version/index.twig', [
            'tigress_core_version' => TIGRESS_CORE_VERSION,
            'framework_helper_version' => $framework_helper_version,
            'display_helper_version' => $display_helper_version,
            'pdf_creator_helper_version' => $pdf_creator_helper_version,
            'tigress_router_version' => $tigress_router_version,
            'tigress_database_version' => $tigress_database_version,
            'tigress_data_converter_version' => $tigress_data_converter_version,
            'tigress_file_manager_version' => $tigress_file_manager_version,
        ]);
    }
}