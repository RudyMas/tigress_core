<?php

namespace Tigress;

/**
 * Class FrameworkHelper - This class is used to create the necessary directories and files for the framework.
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 1.0.0
 * @package Tigress
 */
class FrameworkHelper
{
    /**
     * Create the necessary directories and files for the framework.
     *
     * @return void
     */
    public static function create(): void
    {
        $firstInstall = false;
        if (is_dir('config') === false) {
            $firstInstall = true;
            @mkdir('config');
            @copy('vendor/tigress/core/files/config/config.sample.json', 'config/config.sample.json');
            @copy('vendor/tigress/core/files/.htaccess', 'tests/.htaccess');
        }

        if (is_dir('private') === false) {
            @mkdir('private');
            @copy('vendor/tigress/core/files/.htaccess', 'private/.htaccess');
        }

        if (is_dir('public') === false) {
            @mkdir('public/css', 0777, true);
            @mkdir('public/images', 0777, true);
            @mkdir('public/javascript', 0777, true);
            @copy('vendor/tigress/core/files/.gitkeep', 'public/css/.gitkeep');
            @copy('vendor/tigress/core/files/.gitkeep', 'public/images/.gitkeep');
            @copy('vendor/tigress/core/files/.gitkeep', 'public/javascript/.gitkeep');
        }

        if (is_dir('src') === false) {
            @mkdir('src/controllers', 0777, true);
            @mkdir('src/models', 0777, true);
            @mkdir('src/repositories', 0777, true);
            @mkdir('src/services', 0777, true);
            @mkdir('src/views', 0777, true);
            @copy('vendor/tigress/core/files/.gitkeep', 'src/controllers/.gitkeep');
            @copy('vendor/tigress/core/files/.gitkeep', 'src/models/.gitkeep');
            @copy('vendor/tigress/core/files/.gitkeep', 'src/repositories/.gitkeep');
            @copy('vendor/tigress/core/files/.gitkeep', 'src/services/.gitkeep');
            @copy('vendor/tigress/core/files/.gitkeep', 'src/views/.gitkeep');
            @copy('vendor/tigress/core/files/.htaccess', 'src/.htaccess');
        }

        if (is_dir('system') === false) {
            @mkdir('system');
            @copy('vendor/tigress/core/files/system/config.json', 'system/config.json');
            @copy('vendor/tigress/core/files/.htaccess', 'tests/.htaccess');
        }

        if (file_exists('system/config.json') === false) {
            @copy('vendor/tigress/core/files/system/config.json', 'system/config.json');
            file_put_contents('system/version.txt', TIGRESS_CORE_VERSION);
        }

        if (is_dir('tests') === false) {
            @mkdir('tests');
            @copy('vendor/tigress/core/files/.htaccess', 'tests/.htaccess');
        }

        if (file_exists('config/config.json') === false) {
            if ($firstInstall) {
                print('Installation is complete. You can now create the config/config.json file. Use config/config.sample.json as a starting point.');
            } else {
                print('The file config/config.json does not exist. Please create this file and try again.');
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
        @copy('vendor/tigress/core/files/config/config.sample.json', 'config/config.sample.json');
        @copy('vendor/tigress/core/files/system/config.json', 'system/config.json');
        file_put_contents('system/version.txt', TIGRESS_CORE_VERSION);
    }
}