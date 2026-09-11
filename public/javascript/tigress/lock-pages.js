/**
 * Tigress Lock Pages JavaScript module
 * Split from Tigress.js @version 2026.09.03.0
 */

import { __ } from './translations.js';

/**
 * Lock-pages heartbeat (resource/resourceId)
 * @version 2026.01.08.0
 *
 * Server contract (JSON):
 *  - success: { ok: true, expires_at?: "YYYY-MM-DD HH:MM:SS" }
 *  - locked by other: { ok: false, locked: true, locked_by_user_id?: number }
 *  - no lock: { ok: false, reason: "no_lock" }  (optioneel)
 */
(function () {
    window.tigress = window.tigress || {};

    let timer = null;
    let cfg = null;

    const DEFAULTS = {
        intervalMs: 2 * 60 * 1000, // 2 min (expiry=5 min -> safe)
        refreshUrl: '/lock-pages/refresh',
        releaseUrl: '/lock-pages/release',
        refreshOnVisibility: true,
        releaseOnUnload: true,

        // callbacks
        onOk: null,        // function(data) {}
        onLocked: null,    // function(data) {}
        onError: null      // function(error) {}
    };

    async function refresh() {
        if (!cfg) return;

        try {
            const res = await fetch(cfg.refreshUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    resource: cfg.resource,
                    resourceId: cfg.resourceId
                })
            });

            if (!res.ok) return;

            const data = await res.json();

            if (data && data.ok) {
                if (typeof cfg.onOk === 'function') cfg.onOk(data);
                return;
            }

            // If locked by someone else (or we lost lock)
            if (data && data.locked) {
                stopInternal();

                if (typeof cfg.onLocked === 'function') {
                    cfg.onLocked(data);
                } else {
                    alert(__('This page is currently being edited by someone else. Please try again later.'));
                }
            }
        } catch (e) {
            if (typeof cfg?.onError === 'function') cfg.onError(e);
        }
    }

    function visibilityHandler() {
        if (!cfg) return;
        if (!document.hidden) refresh();
    }

    function stopInternal() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
        if (cfg?.refreshOnVisibility) {
            document.removeEventListener('visibilitychange', visibilityHandler);
        }
        cfg = null;
    }

    function start(resource, resourceId, options = {}) {
        // stop previous heartbeat if any
        stopInternal();

        cfg = Object.assign({}, DEFAULTS, options, {
            resource,
            resourceId
        });

        // Start interval + immediate refresh
        refresh();
        timer = setInterval(refresh, cfg.intervalMs);

        if (cfg.refreshOnVisibility) {
            document.addEventListener('visibilitychange', visibilityHandler);
        }
    }

    function stop() {
        stopInternal();
    }

    // Release lock on unload (best-effort)
    window.addEventListener('beforeunload', () => {
        if (!cfg || !cfg.releaseOnUnload) return;

        try {
            const res = fetch(cfg.releaseUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    resource: cfg.resource,
                    resourceId: cfg.resourceId
                })
            });
        } catch (e) {
            // ignore
        }
    });

    // Expose API
    window.tigress.lockPages = {
        start,
        stop,
        refresh
    };
})();

export const lockPages = window.tigress.lockPages;
