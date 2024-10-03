<?php

namespace Tigress;

/**
 * Class FrameworkHelper - This class is used to create the necessary directories and files for the framework.
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 1.0.2
 * @lastmodified 2024-10-03
 * @package Tigress\FrameworkHelper
 */
class FrameworkHelper
{
    /**
     * Get the version of the FrameworkHelper
     *
     * @return string
     */
    public static function version(): string
    {
        return '1.0.2';
    }

    /**
     * Create the necessary directories and files for the framework.
     *
     * @return void
     */
    public static function create(): void
    {
        $firstInstall = false;
        if (is_dir(SYSTEM_ROOT . '/config') === false) {
            $firstInstall = true;
            @mkdir(SYSTEM_ROOT . '/config');
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/config/config.sample.json',
                SYSTEM_ROOT . '/config/config.sample.json'
            );
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/config/routes.sample.json',
                SYSTEM_ROOT . '/config/routes.sample.json'
            );
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.htaccess',
                SYSTEM_ROOT . '/config/.htaccess'
            );
        }

        if (is_dir(SYSTEM_ROOT . '/private') === false) {
            @mkdir(SYSTEM_ROOT . '/private');
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.htaccess',
                SYSTEM_ROOT . '/private/.htaccess'
            );
        }

        if (is_dir(SYSTEM_ROOT . '/public') === false) {
            @mkdir(SYSTEM_ROOT . '/public/css', 0777, true);
            @mkdir(SYSTEM_ROOT . '/public/images', 0777, true);
            @mkdir(SYSTEM_ROOT . '/public/javascript', 0777, true);
            @mkdir(SYSTEM_ROOT . '/public/json', 0777, true);
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.gitkeep',
                SYSTEM_ROOT . '/public/css/.gitkeep'
            );
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.gitkeep',
                SYSTEM_ROOT . '/public/images/.gitkeep'
            );
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.gitkeep',
                SYSTEM_ROOT . '/public/javascript/.gitkeep'
            );
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.gitkeep',
                SYSTEM_ROOT . '/public/json/.gitkeep'
            );
        }

        if (is_dir(SYSTEM_ROOT . '/src') === false) {
            @mkdir(SYSTEM_ROOT . '/src/controllers', 0777, true);
            @mkdir(SYSTEM_ROOT . '/src/models', 0777, true);
            @mkdir(SYSTEM_ROOT . '/src/repositories', 0777, true);
            @mkdir(SYSTEM_ROOT . '/src/services', 0777, true);
            @mkdir(SYSTEM_ROOT . '/src/views', 0777, true);
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.gitkeep',
                SYSTEM_ROOT . '/src/controllers/.gitkeep'
            );
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.gitkeep',
                SYSTEM_ROOT . '/src/models/.gitkeep'
            );
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.gitkeep',
                SYSTEM_ROOT . '/src/repositories/.gitkeep'
            );
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.gitkeep',
                SYSTEM_ROOT . '/src/services/.gitkeep'
            );
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.gitkeep',
                SYSTEM_ROOT . '/src/views/.gitkeep'
            );
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/src/views/base.twig',
                SYSTEM_ROOT . '/src/views/base.twig'
            );
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.htaccess',
                SYSTEM_ROOT . '/src/.htaccess'
            );
        }

        if (is_dir(SYSTEM_ROOT . '/system') === false) {
            @mkdir(SYSTEM_ROOT . '/system');
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.htaccess',
                SYSTEM_ROOT . '/system/.htaccess'
            );
        }

        if (file_exists(SYSTEM_ROOT . '/system/config.json') === false) {
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/system/config.json',
                SYSTEM_ROOT . '/system/config.json'
            );
            file_put_contents(SYSTEM_ROOT . '/system/initial_version.txt', TIGRESS_CORE_VERSION);
        }

        if (is_dir('tests') === false) {
            @mkdir('tests');
            @copy(
                SYSTEM_ROOT . '/vendor/tigress/core/files/.htaccess',
                SYSTEM_ROOT . '/tests/.htaccess'
            );
        }

        if (file_exists('config/config.json') === false) {
            if ($firstInstall) {
                print('Installation is complete. You can now create the config/config.json & config/routes.json file. Use the sample.json-files as a starting point.');
            } else {
                print('The file config/config.json or config/routes.json does not exist. Please create these files and try again.');
            }
            exit;
        }
    }

    /**
     * Update the necessary directories and files for the framework.
     *
     * @return void
     */
    public static function update(): void
    {
        @copy(
            SYSTEM_ROOT . '/vendor/tigress/core/files/config/config.sample.json',
            SYSTEM_ROOT . '/config/config.sample.json'
        );
        @copy(
            SYSTEM_ROOT . '/vendor/tigress/core/files/system/config.json',
            SYSTEM_ROOT . '/system/config.json'
        );
        file_put_contents(SYSTEM_ROOT . 'system/update_version.txt', TIGRESS_CORE_VERSION);
    }
}