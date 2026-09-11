/**
 * Tigress JavaScript module
 * Split from Tigress.js @version 2026.09.03.0
 */

/**
 * @version 2026.01.08.0
 * Universele tigress translation loader and __-functie
 * Load 1 or more translation files and combine them smartly.
 * You can use __('Welkom') to get the translation for "Welkom" in the current language.
 */
(function () {
    let TRANSLATIONS = {};
    let LANG = (document.documentElement.lang || navigator.language || 'en').toLowerCase().substring(0, 2);

    /**
     * Load translation data, either from file(s) or a given object.
     * @param {Array<string>|object} input - Array of URLs to translation JSON files, or an object with translations.
     * @returns {Promise<void>}
     */
    function loadTranslations(input) {
        // Direct object toevoegen, geen fetch
        if (typeof input === "object" && !Array.isArray(input)) {
            for (const [lang, translations] of Object.entries(input)) {
                if (!TRANSLATIONS[lang]) TRANSLATIONS[lang] = {};
                Object.assign(TRANSLATIONS[lang], translations);
            }
            return Promise.resolve(); // interface consistent houden
        }

        // Array van bestanden, zoals vroeger
        return Promise.all(
            input.map(file =>
                fetch(file)
                    .then(r => {
                        if (!r.ok) throw new Error(`Can not load translation file: ${file}`);
                        return r.json();
                    })
                    .catch(e => {
                        console.warn(e);
                        return {};
                    })
            )
        ).then(jsons => {
            TRANSLATIONS = {};
            for (const json of jsons) {
                for (const [lang, translations] of Object.entries(json)) {
                    if (!TRANSLATIONS[lang]) TRANSLATIONS[lang] = {};
                    Object.assign(TRANSLATIONS[lang], translations);
                }
            }
        });
    }

    function __(text) {
        const lang = (window.LANG || LANG);
        if (TRANSLATIONS && TRANSLATIONS[lang] && TRANSLATIONS[lang][text]) {
            return TRANSLATIONS[lang][text];
        }
        return text;
    }

    window.tigress = window.tigress || {};
    window.tigress.loadTranslations = loadTranslations;
    window.__ = __;
})();

export const __ = window.__;
export const loadTranslations = window.tigress.loadTranslations;
