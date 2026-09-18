<?php

namespace Tigress;

use Repository\WebsiteMessagesRepo;

/**
 * Class WebsiteMessagesHelper (PHP version 8.5)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2026 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2026.09.18.2
 * @package Tigress\WebsiteMessagesHelper
 */
class WebsiteMessagesHelper
{
    /**
     * Returns the version of the WebsiteMessagesHelper
     *
     * @return string
     */
    public static function version(): string
    {
        return '2026.09.18';
    }

    public static function getWebsiteMessages(): array
    {
        if (class_exists('Tigress\WebsiteMessages')) {
            // get the current URL path
            $currentUrlPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

            $tigressWebsiteMessagesRepo = new WebsiteMessagesRepo();
            $tigressWebsiteMessagesRepo->loadByWhereQuery("
                (active_from IS NULL OR active_from <= NOW())
                AND (active_until IS NULL OR active_until >= NOW())
                AND active = 1
                ",
                [],
                'id DESC');

            if ($tigressWebsiteMessagesRepo->isEmpty()) {
                return [];
            }

            $pageTop = [];
            $pageBottom = [];
            $popup = [];
            foreach ($tigressWebsiteMessagesRepo as $tigressWebsiteMessage) {
                if (self::checkCurrentUrlPath($currentUrlPath, $tigressWebsiteMessage->url_location)) {
                    switch ($tigressWebsiteMessage->display) {
                        case 'page-top':
                            $pageTop = [
                                'message' => $tigressWebsiteMessage->message,
                                'type' => $tigressWebsiteMessage->type,
                            ];
                            break;
                        case 'page-bottom':
                            $pageBottom = [
                                'message' => $tigressWebsiteMessage->message,
                                'type' => $tigressWebsiteMessage->type,
                            ];
                            break;
                        case 'popup':
                            $popup[] = [
                                'title' => $tigressWebsiteMessage->title,
                                'message' => $tigressWebsiteMessage->message,
                            ];
                            break;
                    }
                }
            }

            return [
                'page_top' => $pageTop,
                'page_bottom' => $pageBottom,
                'popup' => $popup,
            ];
        } else {
            return [];
        }
    }

    /**
     * Check if the current URL path matches the given URL location pattern.
     *
     * Extra conditions can be added:
     * /google-doc?session=groepId,1
     * /overzicht?cookie=layout,compact
     * /documenten?get=status,archive
     *
     * @param mixed $currentUrlPath
     * @param string $url_location
     * @return bool
     */
    private static function checkCurrentUrlPath(mixed $currentUrlPath, string $url_location): bool
    {
        // Split URL pattern and optional condition
        [$urlPattern, $condition] = array_pad(
            explode('?', $url_location, 2),
            2,
            null
        );

        // Check URL path
        $urlPattern = str_replace('*', '__WILDCARD__', $urlPattern);
        $pattern = preg_quote($urlPattern, '/');
        $pattern = str_replace('__WILDCARD__', '[^\/]+', $pattern);

        if (preg_match('/^' . $pattern . '$/', $currentUrlPath) !== 1) {
            return false;
        }

        // No extra condition = URL match is enough
        if (empty($condition)) {
            return true;
        }

        // Split condition: session=groepId,1
        [$type, $parameters] = array_pad(
            explode('=', $condition, 2),
            2,
            null
        );

        if (!$type || !$parameters) {
            return false;
        }

        [$key, $value] = array_pad(
            explode(',', $parameters, 2),
            2,
            null
        );

        if ($key === null || $value === null) {
            return false;
        }

        return match ($type) {
            'session' => isset($_SESSION[$key])
                && (string) $_SESSION[$key] === $value,

            'cookie' => isset($_COOKIE[$key])
                && (string) $_COOKIE[$key] === $value,

            'get' => isset($_GET[$key])
                && (string) $_GET[$key] === $value,

            default => false,
        };
    }
}