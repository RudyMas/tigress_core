<?php

namespace Controller\Core;

use Repository\SystemLockPagesRepo;
use Repository\UsersRepo;

/**
 * Class LockPagesController (PHP version 8.5)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2026.01.08.1
 * @package Controller\Core\LockPagesController
 */
class LockPagesController
{
    /**
     * Get the version of the class
     *
     * @return string
     */
    public static function version(): string
    {
        return '2026.01.08';
    }

    /**
     *  Check if a page is locked
     *
     * @param string $resource
     * @param int $resourceId
     * @return bool
     */
    public function checkIfPageIsLocked(string $resource, int $resourceId): bool
    {
        $systemLockPageRepo = new SystemLockPagesRepo();

        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + 300);

        // delete expired locks
        $sql = "DELETE FROM system_lock_pages
                WHERE expires_at < :current_time";
        $systemLockPageRepo->deleteByQuery($sql, [
            ':current_time' => $now
        ]);

        $systemLockPageRepo->reset();
        $systemLockPageRepo->loadByPrimaryKey([
            'resource' => $resource,
            'resource_id' => $resourceId
        ]);

        if ($systemLockPageRepo->isEmpty()) {
            $systemLockPageRepo->new();
            $systemLockPage = $systemLockPageRepo->current();
            $systemLockPage->resource = $resource;
            $systemLockPage->resource_id = $resourceId;
            $systemLockPage->locked_by_user_id = $_SESSION['user']['id'] ?? null;
            $systemLockPage->locked_at = $now;
            $systemLockPage->expires_at = $expires;
            $systemLockPageRepo->save($systemLockPage);
            return false;
        }

        $systemLockPage = $systemLockPageRepo->current();

        if ($systemLockPage->expires_at > $now && $systemLockPage->locked_by_user_id === ($_SESSION['user']['id'] ?? null)) {
            $systemLockPage->expires_at = $expires;
            $systemLockPageRepo->save($systemLockPage);
            return false;
        }

        $usersRepo = new UsersRepo();
        $usersRepo->loadById($systemLockPage->locked_by_user_id);
        $user = $usersRepo->current();

        $infoMessage = sprintf(__('This page is currently being edited by %s since %s. Please try again later.'),
            htmlspecialchars($user->first_name . ' ' . $user->last_name),
            date('d-m-Y, H:i:s', strtotime($systemLockPage->locked_at))
        );

        $_SESSION['message'] = $infoMessage;

        return true;
    }

    /**
     * Refresh a page lock
     *
     * @param array $args
     * @param string $body
     * @return array
     */
    public function refreshLock(array $args, string $body): array
    {
        $bodyData = json_decode($body, true);

        $systemLockPagesRepo = new SystemLockPagesRepo();

        $systemLockPagesRepo->loadByPrimaryKey([
            'resource' => $bodyData['resource'],
            'resource_id' => $bodyData['resourceId'],
        ]);

        if ($systemLockPagesRepo->isEmpty()) {
            return ['ok' => false, 'reason' => 'no_lock'];
        }

        $lock = $systemLockPagesRepo->current();
        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + 300);
        $currentUserId = $_SESSION['user']['id'] ?? null;

        // Als iemand anders de lock heeft en niet verlopen is
        if ($lock->locked_by_user_id !== $currentUserId && $lock->expires_at >= $now) {
            return ['ok' => false, 'locked' => true, 'locked_by_user_id' => $lock->locked_by_user_id];
        }

        // Indien verlopen
        $lock->locked_by_user_id = $currentUserId;
        $lock->expires_at = $expires;
        $systemLockPagesRepo->save($lock);

        return ['ok' => true, 'expires_at' => $expires];
    }

    /**
     * Release a page lock
     *
     * @param array $args
     * @param string $body
     * @return array|true[]
     */
    public function releaseLock(array $args, string $body): array
    {
        $bodyData = json_decode($body, true);

        $systemLockPagesRepo = new SystemLockPagesRepo();

        $systemLockPagesRepo->loadByPrimaryKey([
            'resource' => $bodyData['resource'],
            'resource_id' => $bodyData['resourceId'],
        ]);

        if ($systemLockPagesRepo->isEmpty()) {
            return ['ok' => false, 'reason' => 'no_lock'];
        }

        $lock = $systemLockPagesRepo->current();
        $currentUserId = $_SESSION['user']['id'] ?? null;

        // Als iemand anders de lock heeft
        if ($lock->locked_by_user_id !== $currentUserId) {
            return ['ok' => false, 'locked' => true, 'locked_by_user_id' => $lock->locked_by_user_id];
        }

        $systemLockPagesRepo->deleteByPrimaryKey([
            'resource' => $bodyData['resource'],
            'resource_id' => $bodyData['resourceId'],
        ]);

        return ['ok' => true];
    }

    /**
     * Remove a page lock
     *
     * @param string $resource
     * @param int $resourceId
     * @return void
     */
    public function removePageLock(string $resource, int $resourceId): void
    {
        $systemLockPageRepo = new SystemLockPagesRepo();
        $systemLockPageRepo->deleteByPrimaryKey([
            'resource' => $resource,
            'resource_id' => $resourceId
        ]);
    }
}