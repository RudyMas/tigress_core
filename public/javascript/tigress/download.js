/**
 * Tigress Download JavaScript module
 * Split from Tigress.js @version 2026.09.03.0
 */

import { __ } from './translations.js';

// Showing download progress on the website
const TigressDownload = (() => {
    const getFilenameFromDisposition = (disposition, fallback) => {
        if (!disposition) {
            return fallback;
        }

        const utf8Match = disposition.match(/filename\*=UTF-8''([^;\n]+)/i);

        if (utf8Match?.[1]) {
            return decodeURIComponent(utf8Match[1]);
        }

        const match = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/i);

        return match?.[1]?.replace(/['"]/g, '') || fallback;
    };

    const resolveModal = (modal, modalId) => {
        if (modal) {
            return modal;
        }

        if (!modalId || typeof bootstrap === 'undefined') {
            return null;
        }

        const modalElement = document.getElementById(modalId);

        return modalElement
            ? bootstrap.Modal.getOrCreateInstance(modalElement)
            : null;
    };

    const resolveElement = (element, id) => {
        if (element) {
            return element;
        }

        return id ? document.getElementById(id) : null;
    };

    const showModal = (modal) => {
        if (!modal) {
            return Promise.resolve();
        }

        const modalElement = modal._element;

        if (!modalElement) {
            return Promise.resolve();
        }

        if (modalElement.classList.contains('show')) {
            return Promise.resolve();
        }

        return new Promise(resolve => {
            modalElement.addEventListener(
                'shown.bs.modal',
                () => resolve(),
                { once: true }
            );

            modal.show();
        });
    };

    const updateProgress = (
        bar,
        text,
        percent,
        loadedBytes,
        totalBytes
    ) => {
        if (bar) {
            bar.style.width = `${percent}%`;
            bar.setAttribute('aria-valuenow', String(percent));
            bar.textContent = `${percent}%`;
        }

        if (text && totalBytes) {
            text.textContent =
                `${__('Busy downloading')}: ` +
                `${(loadedBytes / 1024).toFixed(0)} KB / ` +
                `${(totalBytes / 1024).toFixed(0)} KB...`;
        }
    };

    const download = async (url, options = {}) => {
        let {
            method = 'GET',
            filename = 'download',
            fetcher = window.fetch.bind(window),
            fetchOptions = {},

            modal = null,
            modalId = null,

            progressBar = null,
            progressBarId = 'downloadProgressBar',

            progressText = null,
            progressTextId = 'downloadProgressText',

            messages = {}
        } = options;

        modal = resolveModal(modal, modalId);
        progressBar = resolveElement(progressBar, progressBarId);
        progressText = resolveElement(progressText, progressTextId);

        const msg = {
            connecting: __('Connecting...'),
            downloading: __('Downloading file...'),
            saving: __('Saving file...'),
            error: __('Error downloading: '),
            ...messages
        };

        try {
            if (progressBar) {
                progressBar.style.width = '0%';
                progressBar.setAttribute('aria-valuenow', '0');
                progressBar.textContent = '0%';
            }

            if (progressText) {
                progressText.textContent = msg.connecting;
            }

            // Wait until the Bootstrap modal is fully visible.
            await showModal(modal);

            const response = await fetcher(url, {
                method,
                credentials: 'same-origin',
                ...fetchOptions
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const disposition = response.headers.get(
                'content-disposition'
            );

            const finalFilename = getFilenameFromDisposition(
                disposition,
                filename
            );

            const contentLength = response.headers.get(
                'content-length'
            );

            let blob;

            if (!contentLength || !response.body) {
                if (progressText) {
                    progressText.textContent = msg.downloading;
                }

                blob = await response.blob();
            } else {
                const totalBytes = parseInt(contentLength, 10);
                let loadedBytes = 0;

                const reader = response.body.getReader();

                const stream = new ReadableStream({
                    async start(controller) {
                        try {
                            while (true) {
                                const { done, value } =
                                    await reader.read();

                                if (done) {
                                    controller.close();
                                    break;
                                }

                                loadedBytes += value.byteLength;

                                const percent = Math.min(
                                    100,
                                    Math.round(
                                        (loadedBytes / totalBytes) * 100
                                    )
                                );

                                updateProgress(
                                    progressBar,
                                    progressText,
                                    percent,
                                    loadedBytes,
                                    totalBytes
                                );

                                controller.enqueue(value);
                            }
                        } catch (error) {
                            controller.error(error);
                        }
                    }
                });

                blob = await new Response(stream).blob();
            }

            if (progressBar) {
                progressBar.style.width = '100%';
                progressBar.setAttribute('aria-valuenow', '100');
                progressBar.textContent = '100%';
            }

            if (progressText) {
                progressText.textContent = msg.saving;
            }

            const blobUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');

            a.style.display = 'none';
            a.href = blobUrl;
            a.download = finalFilename;

            document.body.appendChild(a);

            a.click();

            // Give the browser a moment to start handling the download
            // before removing the temporary link and Blob URL.
            setTimeout(() => {
                window.URL.revokeObjectURL(blobUrl);
                a.remove();
            }, 100);

            return true;
        } catch (error) {
            alert(msg.error + error.message);
            return false;
        } finally {
            if (modal) {
                modal.hide();
            }
        }
    };

    return {
        download
    };
})();

document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-download-file');

    if (!button) {
        return;
    }

    event.preventDefault();

    TigressDownload.download(button.dataset.url + '?t=' + Date.now(), {
        filename:
            button.dataset.filename || 'download.pdf',

        modalId:
            button.dataset.modalId || 'downloadProgressModal'
    });
});

// Backwards-compatible global
window.TigressDownload = TigressDownload;

export { TigressDownload };
