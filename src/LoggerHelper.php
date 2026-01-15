<?php

namespace Tigress;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;

/**
 * Class LoggerHelper (PHP version 8.5)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2025-2026 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2026.01.15.0
 * @package Tigress\LoggerHelper
 */
class LoggerHelper
{
    /**
     * Returns the version of the LoggerHelper
     *
     * @return string
     */
    public static function version(): string
    {
        return '2026.01.15';
    }

    /**
     * Creates a Monolog logger with support for:
     * - date-based filenames
     * - optional rotation
     *
     * @param string $channelName The name of the log channel (e.g. 'smartschool')
     * @param Level $level Logging level (e.g. Level::Error, Level::Debug, etc. - Default: Level::Error)
     * @param int $retentionDays Number of days to keep log files (0 = no rotation, keep everything - Default: 30)
     * @param string|null $dateFormat e.g. 'Y-m-d' for daily logs (null = no date suffix - Default: 'Y-m-d')
     * @return LoggerInterface
     * @param string|null $logDirectory Path to the log directory (default: SYSTEM_ROOT . '/logs')
     */
    public static function create(
        string  $channelName,
        Level   $level = Level::Error,
        int     $retentionDays = 30,
        ?string $dateFormat = 'Y-m-d',
        ?string $logDirectory = null,
    ): LoggerInterface
    {
        if (!$logDirectory) {
            $logDirectory = SYSTEM_ROOT . '/logs';
        }

        if (!is_dir($logDirectory)) {
            mkdir($logDirectory, 0775, true);
        }

        $date = $dateFormat ? date($dateFormat) : '';
        $fileName = $date ? "{$channelName}_{$date}.log" : "{$channelName}.log";
        $basePath = $logDirectory . '/' . $channelName;

        $logger = new Logger($channelName);

        if ($retentionDays > 0) {
            // Use RotatingFileHandler (automatically removes old logs)
            $handler = new RotatingFileHandler($basePath, $retentionDays, $level);
        } else {
            // Keep all logs without rotation (add date to filename)
            $handler = new StreamHandler($logDirectory . '/' . $fileName, $level);
        }

        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context%\n",
            "Y-m-d H:i:s",
            true,
            true
        );
        $handler->setFormatter($formatter);

        $logger->pushHandler($handler);

        return $logger;
    }

    /**
     * Returns a default logger with predefined configuration.
     *
     * @return LoggerInterface
     */
    public static function getDefault(): LoggerInterface
    {
        return self::create(channelName: 'tigress');
    }
}
