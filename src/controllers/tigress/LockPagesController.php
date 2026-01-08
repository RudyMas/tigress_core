<?php

namespace Controller\tigress;

use Repository\SystemLockPagesRepo;
use Repository\UsersRepo;
use Tigress\Core;

/**
 * Class LockPagesController (PHP version 8.5)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2026.01.08.0
 * @package Controller\tigress\LockPagesController
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
            htmlspecialchars($systemLockPage->locked_at)
        );

        $_SESSION['message'] = $infoMessage;

        return true;
    }

    /**
     * Refresh a page lock
     *
     * @param array $args
     * @return array
     */
    public function refreshLock(array $args): array
    {
        Core::dump($args);

        $repo = new SystemLockPagesRepo();

        $repo->loadByPrimaryKey([
            'resource' => $resource,
            'resource_id' => $resourceId
        ]);

        if ($repo->isEmpty()) {
            return ['ok' => false, 'reason' => 'no_lock'];
        }

        $lock = $repo->current();
        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + 300);
        $currentUserId = $_SESSION['user']['id'] ?? null;

        // Als iemand anders de lock heeft en niet verlopen is
        if ($lock->locked_by_user_id !== $currentUserId && $lock->expires_at >= $now) {
            return ['ok' => false, 'locked' => true, 'locked_by_user_id' => $lock->locked_by_user_id];
        }

        // Indien verlopen
        $lock->locked_by_user_id = $currentUserId;
        $lock->locked_at = $now;
        $lock->expires_at = $expires;
        $repo->save($lock);

        return ['ok' => true, 'expires_at' => $expires];
    }

    /**
     * Release a page lock
     *
     * @param array $args
     * @return array|true[]
     */
    public function releaseLock(array $args): array
    {
        $repo = new SystemLockPagesRepo();

        $repo->loadByPrimaryKey([
            'resource' => $args['resource'],
            'resource_id' => $args['resource_id']
        ]);

        if ($repo->isEmpty()) {
            return ['ok' => false, 'reason' => 'no_lock'];
        }

        $lock = $repo->current();
        $currentUserId = $_SESSION['user']['id'] ?? null;

        // Als iemand anders de lock heeft
        if ($lock->locked_by_user_id !== $currentUserId) {
            return ['ok' => false, 'locked' => true, 'locked_by_user_id' => $lock->locked_by_user_id];
        }

        $repo->deleteByPrimaryKey([
            'resource' => $args['resource'],
            'resource_id' => $args['resource_id']
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