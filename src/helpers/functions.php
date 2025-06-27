<?php

function __(string $word, array $translations = []): string
{
    $lang = substr(CONFIG->website->html_lang, 0, 2);
    if (isset($translations[$lang]) && isset($translations[$lang][$word])) {
        return $translations[$lang][$word];
    }
    return $word;
}
