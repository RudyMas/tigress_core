<?php

namespace Controller\version;

use Controller\Core\GoogleDriveController;
use Controller\Core\SettingsController;
use Controller\Menu;
use Exception;
use Tigress\Communication;
use Tigress\Controller;
use Tigress\Database;
use Tigress\DataConverter;
use Tigress\DataFiles;
use Tigress\DisplayHelper;
use Tigress\Encryption;
use Tigress\FormBuilder;
use Tigress\FileManager;
use Tigress\FormViewer;
use Tigress\FrameworkHelper;
use Tigress\GoogleApi;
use Tigress\HttpRequests;
use Tigress\KanbanBoard;
use Tigress\LoggerHelper;
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
 * Class VersionController (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.10.14.0
 * @package Controller\version
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

        if (isset(CONFIG->website->naughty) && CONFIG->website->naughty) {
            $image = file_get_contents(SYSTEM_ROOT . '/vendor/tigress/core/public/images/tigress_naughty.txt');
        } else {
            $image = BASE_URL . '/vendor/tigress/core/public/images/tigress.jpg';
        }

        // Get the version of the main Tigress Classes loaded
        $tigress_security_version = class_exists('Tigress\Security') ? Security::version() : 'Not Active';
        $tigress_router_version = class_exists('Tigress\Router') ? Router::version() : 'Not Active';
        $tigress_rights_version = class_exists('Tigress\Rights') ? Rights::version() : 'Not Active';
        $tigress_menu_version = class_exists('Controller\Menu') ? Menu::version() : 'Not Active';
        $tigress_encryption_version = class_exists('Tigress\Encryption') ? Encryption::version() : 'Not Active';
        $tigress_controller_version = class_exists('Tigress\Controller') ? Controller::version() : 'Not Active';

        // Get the version of the Tigress Core Helper Classes loaded
        $framework_helper_version = class_exists('Tigress\FrameworkHelper') ? FrameworkHelper::version() : 'Not Active';
        $display_helper_version = class_exists('Tigress\DisplayHelper') ? DisplayHelper::version() : 'Not Active';
        $pdf_creator_helper_version = class_exists('Tigress\PdfCreatorHelper') ? PdfCreatorHelper::version() : 'Not Active';
        $logger_helper_version = class_exists('Tigress\LoggerHelper') ? LoggerHelper::version() : 'Not Active';

        // Get the version of the Tigress Controller Classes loaded
        $google_drive_controller_version = class_exists('Controller\Core\GoogleDriveController') ? GoogleDriveController::version() : 'Not Active';
        $settings_controller_version = class_exists('Controller\Core\SettingsController') ? SettingsController::version() : 'Not Active';

        // Get the version of the Tigress Database Classes loaded
        $tigress_database_version = class_exists('Tigress\Database') && CONFIG->packages->tigress_database ? Database::version() : 'Not Active';
        $tigress_repository_version = class_exists('Tigress\Repository') ? Repository::version() : 'Not Active';
        $tigress_model_version = class_exists('Tigress\Model') ? Model::version() : 'Not Active';

        // Get the version of the Tigress Support Classes loaded
        $tigress_data_converter_version = class_exists('Tigress\DataConverter') ? DataConverter::version() : 'Not Active';
        $tigress_data_files_version = class_exists('Tigress\DataFiles') ? DataFiles::version() : 'Not Active';
        $tigress_file_manager_version = class_exists('Tigress\FileManager') ? FileManager::version() : 'Not Active';
        $tigress_http_requests_version = class_exists('Tigress\HttpRequests') ? HttpRequests::version() : 'Not Active';
        $tigress_communication_version = class_exists('Tigress\Communication') ? Communication::version() : ['Communication' => 'Not Active'];
        $tigress_google_api_version = class_exists('Tigress\GoogleApi') ? GoogleApi::version() : ['GoogleApi' => 'Not Active'];
        $tigress_manipulator_version = class_exists('Tigress\Manipulator') ? Manipulator::version() : ['Manipulator' => 'Not Active'];

        // Get the version of the standalone Tigress Modules loaded
        $tigress_kanban_board_version = class_exists('Tigress\KanbanBoard') ? KanbanBoard::version() : 'Not Active';
        $tigress_form_builder_version = class_exists('Tigress\FormBuilder') ? FormBuilder::version() : 'Not Active';
        $tigress_form_viewer_version = class_exists('Tigress\FormViewer') ? FormViewer::version() : 'Not Active';
        $tigress_users_version = class_exists('Tigress\Users') ? \Tigress\Users::version() : 'Not Active';

        TWIG->render('version/index.twig', [
            'image' => $image,
            'tigress_core_version' => TIGRESS_CORE_VERSION,
            'tigress_security_version' => $tigress_security_version,
            'tigress_router_version' => $tigress_router_version,
            'tigress_rights_version' => $tigress_rights_version,
            'tigress_menu_version' => $tigress_menu_version,
            'tigress_encryption_version' => $tigress_encryption_version,
            'tigress_controller_version' => $tigress_controller_version,
            'framework_helper_version' => $framework_helper_version,
            'display_helper_version' => $display_helper_version,
            'pdf_creator_helper_version' => $pdf_creator_helper_version,
            'logger_helper_version' => $logger_helper_version,
            'google_drive_controller_version' => $google_drive_controller_version,
            'settings_controller_version' => $settings_controller_version,
            'tigress_database_version' => $tigress_database_version,
            'tigress_repository_version' => $tigress_repository_version,
            'tigress_model_version' => $tigress_model_version,
            'tigress_data_converter_version' => $tigress_data_converter_version,
            'tigress_data_files_version' => $tigress_data_files_version,
            'tigress_file_manager_version' => $tigress_file_manager_version,
            'tigress_http_requests_version' => $tigress_http_requests_version,
            'tigress_communication_version' => $tigress_communication_version,
            'tigress_google_api_version' => $tigress_google_api_version,
            'tigress_manipulator_version' => $tigress_manipulator_version,
            'tigress_kanban_board_version' => $tigress_kanban_board_version,
            'tigress_form_builder_version' => $tigress_form_builder_version,
            'tigress_form_viewer_version' => $tigress_form_viewer_version,
            'tigress_users_version' => $tigress_users_version,
        ]);
    }
}