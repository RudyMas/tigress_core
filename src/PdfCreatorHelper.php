<?php

namespace Tigress;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Class PdfCreatorClass (PHP version 8.4)
 * - This class is used to create a PDF file from a HTML string.
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024-2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.04.29.0
 * @package Tigress\PdfCreatorHelper
 */
class PdfCreatorHelper
{
    private Dompdf $Dompdf;
    private string $language = 'nl';

    /**
     * Get the version of the PdfCreatorHelper
     *
     * @return string
     */
    public static function version(): string
    {
        return '2025.04.29';
    }

    /**
     * @param array|Options|null $config
     */
    public function __construct(array|Options|null $config = null)
    {
        $this->Dompdf = new Dompdf($config);
    }

    /**
     * This function creates a PDF file from an HTML string.
     *
     * @param string $html
     * @param string $format
     * @param string $orientation
     * @param string $filename
     * @param string $filepath
     * @param bool $pagination
     * @param int $attachment
     * @return void
     */
    public function createPdf(
        string $html,
        string $format = 'A4',
        string $orientation = 'portrait',
        string $filename = 'document.pdf',
        string $filepath = '/public/tmp/',
        bool   $pagination = false,
        int    $attachment = 1
    ): void
    {
        $this->Dompdf->loadHtml($html);
        $this->Dompdf->setPaper($format, $orientation);
        $this->Dompdf->render();

        if ($pagination) {
            $canvas = $this->Dompdf->getCanvas();
            $page = [
                'A3' => [
                    'portrait' => ['x' => 380, 'y' => 1160],
                    'landscape' => ['x' => 1090, 'y' => 810],
                ],
                'A4' => [
                    'portrait' => ['x' => 270, 'y' => 820],
                    'landscape' => ['x' => 750, 'y' => 575],
                ],
                'A5' => [
                    'portrait' => ['x' => 170, 'y' => 575],
                    'landscape' => ['x' => 500, 'y' => 390],
                ],
            ];

            $paginationText = $this->getPaginationText();

            $canvas->page_text(
                $page[$format][$orientation]['x'],
                $page[$format][$orientation]['y'],
                $paginationText,
                null,
                8
            );
        }

        if ($attachment === 2) {
            // check if $filepath starts and ends with a slash
            ($filepath[0] === '/') ?: $filepath = '/' . $filepath;
            ($filepath[strlen($filepath) - 1] === '/') ?: $filepath = $filepath . '/';

            $workingDir = SYSTEM_ROOT . $filepath;
            if (!is_dir($workingDir)) {
                mkdir($workingDir, 0777, true);
            }
            file_put_contents($workingDir . $filename, $this->Dompdf->output());
            return;
        }

        $this->Dompdf->stream($filename, ['Attachment' => $attachment]);
    }

    /**
     * This function returns an image as a base64 string.
     *
     * @param string $image
     * @param string $alt
     * @param array|null $options
     * @return string
     */
    public function getImage(string $image, string $alt = 'Logo', ?array $options = null): string
    {
        $image = str_replace('\\', '/', $image);
        if ($image[0] != '/') {
            $image = '/' . $image;
        }
        $file = $_SERVER['DOCUMENT_ROOT'] . $image;
        $base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($file));

        if (is_null($options)) {
            return "<img src='$base64' alt='$alt'/>";
        } else {
            if (isset($options['width']) && isset($options['height'])) {
                return "<img src='$base64' alt='$alt' width='{$options['width']}' height='{$options['height']}'/>";
            } elseif (isset($options['width'])) {
                return "<img src='$base64' alt='$alt' width='{$options['width']}'/>";
            } elseif (isset($options['height'])) {
                return "<img src='$base64' alt='$alt' height='{$options['height']}'/>";
            } else {
                return "<img src='$base64' alt='$alt'/>";
            }
        }
    }

    /**
     * Set the language of the PDF file.
     *
     * @param string $language
     * @return void
     */
    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    private function getPaginationText(): string
    {
        return match ($this->language) {
            'ar' => "الصفحة {PAGE_NUM} من {PAGE_COUNT}",
            'az' => "Səhifə {PAGE_NUM} / {PAGE_COUNT}",
            'be' => "Старонка {PAGE_NUM} з {PAGE_COUNT}",
            'bg' => "Страница {PAGE_NUM} от {PAGE_COUNT}",
            'bs', 'hr' => "Stranica {PAGE_NUM} od {PAGE_COUNT}",
            'cs', 'sk' => "Strana {PAGE_NUM} z {PAGE_COUNT}",
            'da' => "Side {PAGE_NUM} af {PAGE_COUNT}",
            'de' => "Seite {PAGE_NUM} von {PAGE_COUNT}",
            'el' => "Σελίδα {PAGE_NUM} από {PAGE_COUNT}",
            'en' => "Page {PAGE_NUM} of {PAGE_COUNT}",
            'es', 'pt' => "Página {PAGE_NUM} de {PAGE_COUNT}",
            'et' => "Lehekülg {PAGE_NUM} / {PAGE_COUNT}",
            'fi' => "Sivu {PAGE_NUM} / {PAGE_COUNT}",
            'fr' => "Page {PAGE_NUM} sur {PAGE_COUNT}",
            'hi' => "पृष्ठ {PAGE_NUM} का {PAGE_COUNT} में",
            'hu' => "Oldal {PAGE_NUM} / {PAGE_COUNT}",
            'hy' => "Էջ {PAGE_NUM} է {PAGE_COUNT}-ից",
            'id' => "Halaman {PAGE_NUM} dari {PAGE_COUNT}",
            'it' => "Pagina {PAGE_NUM} di {PAGE_COUNT}",
            'ja' => "ページ {PAGE_NUM} / {PAGE_COUNT}",
            'ka' => "გვერდი {PAGE_NUM} სულ {PAGE_COUNT}-დან",
            'kk', 'ky' => "Бет {PAGE_NUM} / {PAGE_COUNT}",
            'ko' => "페이지 {PAGE_NUM} / {PAGE_COUNT}",
            'lt' => "Puslapis {PAGE_NUM} iš {PAGE_COUNT}",
            'lv' => "Lapa {PAGE_NUM} no {PAGE_COUNT}",
            'mk' => "Страна {PAGE_NUM} од {PAGE_COUNT}",
            'mn' => "Хуудас {PAGE_NUM} / {PAGE_COUNT}",
            'ms' => "Halaman {PAGE_NUM} daripada {PAGE_COUNT}",
            'nl' => "Pagina {PAGE_NUM} van {PAGE_COUNT}",
            'no' => "Side {PAGE_NUM} av {PAGE_COUNT}",
            'pl' => "Strona {PAGE_NUM} z {PAGE_COUNT}",
            'ro' => "Pagina {PAGE_NUM} din {PAGE_COUNT}",
            'ru' => "Страница {PAGE_NUM} из {PAGE_COUNT}",
            'sl' => "Stran {PAGE_NUM} od {PAGE_COUNT}",
            'sq' => "Faqja {PAGE_NUM} nga {PAGE_COUNT}",
            'sr' => "Страница {PAGE_NUM} од {PAGE_COUNT}",
            'sv' => "Sida {PAGE_NUM} av {PAGE_COUNT}",
            'th' => "หน้า {PAGE_NUM} จาก {PAGE_COUNT}",
            'tg' => "Саҳифа {PAGE_NUM} аз {PAGE_COUNT}",
            'tr' => "Sayfa {PAGE_NUM} / {PAGE_COUNT}",
            'uk' => "Сторінка {PAGE_NUM} з {PAGE_COUNT}",
            'uz' => "Sahifa {PAGE_NUM} {PAGE_COUNT} dan",
            'vi' => "Trang {PAGE_NUM} của {PAGE_COUNT}",
            'zh' => "第 {PAGE_NUM} 页，共 {PAGE_COUNT} 页",
            default => "Page {PAGE_NUM} of {PAGE_COUNT}",
        };
    }
}