<?php

namespace Tigress;

use Dompdf\Dompdf;

/**
 * Class PdfCreatorClass (PHP version 8.4)
 * - This class is used to create a PDF file from a HTML string.
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024-2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.01.21.0
 * @package Tigress\PdfCreatorHelper
 */
class PdfCreatorHelper
{
    private Dompdf $Dompdf;

    /**
     * Get the version of the PdfCreatorHelper
     *
     * @return string
     */
    public static function version(): string
    {
        return '2025.01.21';
    }

    /**
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->Dompdf = new Dompdf($config);
    }

    /**
     * This function creates a PDF file from a HTML string.
     *
     * @param string $html
     * @param string $format
     * @param string $orientation
     * @param string $filename
     * @param string $filepath
     * @param bool $paginatie
     * @param int $attachment
     * @return void
     */
    public function createPdf(
        string $html,
        string $format = 'A4',
        string $orientation = 'portrait',
        string $filename = 'document.pdf',
        string $filepath = '/public/tmp/',
        bool   $paginatie = false,
        int    $attachment = 1
    ): void
    {
        $this->Dompdf->loadHtml($html);
        $this->Dompdf->setPaper($format, $orientation);
        $this->Dompdf->render();

        if ($paginatie) {
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

            $canvas->page_text(
                $page[$format][$orientation]['x'],
                $page[$format][$orientation]['y'],
                "Pagina {PAGE_NUM} van {PAGE_COUNT}",
                null,
                8
            );
        }

        // Check if $filepath end with a slash else add it
        if (!str_ends_with($filepath, '/')) {
            $filepath .= '/';
        }

        // Check if folder exists, else create it
        $workingDir = SYSTEM_ROOT . $filepath ?? '/';
        if (!is_dir($filepath)) {
            mkdir($workingDir, 0777, true);
        }

        $this->Dompdf->stream($workingDir . $filename, ['Attachment' => $attachment]);
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
}