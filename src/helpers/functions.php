<?php

/**
 * Translate a word/sentence to the current language
 *
 * @param string $text
 * @return string
 */
function __(string $text): string
{
    $lang = substr(CONFIG->website->html_lang ?? 'en', 0, 2);
    return TRANSLATIONS->get()[$lang][$text] ?? $text;
}