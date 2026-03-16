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

/**
 * Check if the data is JSON data
 *
 * @param $string
 * @return bool
 */
function is_json($string): bool
{
    if (!is_string($string)) {
        return false;
    }

    try {
        json_decode($string, true, 512, JSON_THROW_ON_ERROR);
        return true;
    } catch (JsonException) {
        return false;
    }
}