<?php

namespace Tigress;

use InvalidArgumentException;
use RuntimeException;

/**
 * Class TranslationHelper (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024-2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.06.30.0
 * @package Tigress\TranslationHelper
 */
class TranslationHelper
{
    private array $translations = [];

    /**
     * Add a translation file to the list
     *
     * @param string $filePath
     */
    public function load(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("Translation file does not exist: $filePath");
        }

        $loadTranslations = json_decode(file_get_contents($filePath), true);
        if (!is_array($loadTranslations)) {
            throw new RuntimeException("Invalid translation file: $filePath");
        }

        foreach ($loadTranslations as $lang => $translations) {
            if (!isset($this->translations[$lang])) {
                $this->translations[$lang] = [];
            }
            $this->translations[$lang] = array_merge($this->translations[$lang], $translations);
        }
    }

    /**
     * Get the translations for a specific word
     *
     * @return array
     */
    public function get(): array
    {
        return $this->translations;
    }
}