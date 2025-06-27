<?php

/**
 * Translate a word/sentence to the current language
 *
 * @param string $word
 * @return string
 */
function __(string $word): string
{
    static $translations = null;
    if ($translations === null) {
        $translations = json_decode(file_get_contents(SYSTEM_ROOT . '/translations/translations.json'), true);
    }

    $lang = substr(CONFIG->website->html_lang, 0, 2);
    return $translations[$lang][$word] ?? $word;
}