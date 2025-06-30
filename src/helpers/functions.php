<?php

/**
 * Translate a word/sentence to the current language
 *
 * @param string $word
 * @return string
 */
function __(string $word): string
{
    $lang = substr(CONFIG->website->html_lang, 0, 2);
    return TRANSLATIONS->get()[$lang][$word] ?? $word;
}