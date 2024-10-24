<?php

namespace Controller\version;

use Controller\Menu;
use Exception;
use Tigress\Database;
use Tigress\DataConverter;
use Tigress\DisplayHelper;
use Tigress\FileManager;
use Tigress\FrameworkHelper;
use Tigress\GoogleApi;
use Tigress\Manipulator;
use Tigress\Model;
use Tigress\PdfCreatorHelper;
use Tigress\Repository;
use Tigress\Rights;
use Tigress\Router;
use Tigress\Security;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class VersionController (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 0.12.0
 * @lastmodified 2024-10-18
 * @package Controller\version\VersionController
 */
class VersionController
{
    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError|Exception
     */
    public function index($args = []): void
    {
        MENU->setPosition('none');

        // Get the version of the main Tigress Classes loaded
        $tigress_security_version = class_exists('Tigress\Security') ? Security::version() : 'Not Active';
        $tigress_router_version = class_exists('Tigress\Router') ? Router::version() : 'Not Active';
        $tigress_rights_version = class_exists('Tigress\Rights') ? Rights::version() : 'Not Active';
        $tigress_menu_version = class_exists('Controller\Menu') ? Menu::version() : 'Not Active';
        $tigress_database_version = class_exists('Tigress\Database') && CONFIG->packages->tigress_database ? Database::version() : 'Not Active';
        $tigress_repository_version = class_exists('Tigress\Repository') ? Repository::version() : 'Not Active';
        $tigress_model_version = class_exists('Tigress\Model') ? Model::version() : 'Not Active';

        // Get the version of the Tigress Core Helper Classes loaded
        $framework_helper_version = class_exists('Tigress\FrameworkHelper') ? FrameworkHelper::version() : 'Not Active';
        $display_helper_version = class_exists('Tigress\DisplayHelper') ? DisplayHelper::version() : 'Not Active';
        $pdf_creator_helper_version = class_exists('Tigress\PdfCreatorHelper') ? PdfCreatorHelper::version() : 'Not Active';

        // Get the version of the Tigress Support Classes loaded
        $tigress_data_converter_version = class_exists('Tigress\DataConverter') ? DataConverter::version() : 'Not Active';
        $tigress_file_manager_version = class_exists('Tigress\FileManager') ? FileManager::version() : 'Not Active';
        $tigress_google_api_version = class_exists('Tigress\GoogleApi') ? GoogleApi::version() : ['GoogleApi' => 'Not Active'];
        $tigress_manipulator_version = class_exists('Tigress\Manipulator') ? Manipulator::version() : 'Not Active';

        TWIG->render('version/index.twig', [
            'tigress_core_version' => TIGRESS_CORE_VERSION,
            'tigress_security_version' => $tigress_security_version,
            'tigress_router_version' => $tigress_router_version,
            'tigress_rights_version' => $tigress_rights_version,
            'tigress_menu_version' => $tigress_menu_version,
            'tigress_database_version' => $tigress_database_version,
            'tigress_repository_version' => $tigress_repository_version,
            'tigress_model_version' => $tigress_model_version,
            'framework_helper_version' => $framework_helper_version,
            'display_helper_version' => $display_helper_version,
            'pdf_creator_helper_version' => $pdf_creator_helper_version,
            'tigress_data_converter_version' => $tigress_data_converter_version,
            'tigress_file_manager_version' => $tigress_file_manager_version,
            'tigress_google_api_version' => $tigress_google_api_version,
            'tigress_manipulator_version' => $tigress_manipulator_version,
        ]);
    }
}