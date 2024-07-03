<?php

namespace Tigress;

use Dompdf\Dompdf;

/**
 * Class PdfCreatorClass - This class is used to create a PDF file from a HTML string.
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 1.0.0
 * @lastmodified 2024-07-01
 * @package Tigress
 */
class PdfCreatorHelper
{
    private Dompdf $Dompdf;

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
     * @param bool $paginatie
     * @param int $attachment
     * @return void
     */
    public function createPdf(
        string $html,
        string $format = 'A4',
        string $orientation = 'portrait',
        string $filename = 'document.pdf',
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

        $this->Dompdf->stream($filename, ['Attachment' => $attachment]);
    }

    /**
     * This function returns an image as a base64 string.
     *
     * @param string $image
     * @param string $alt
     * @param null $width
     * @param null $height
     * @return string
     */
    public function getImage(string $image, string $alt = 'Logo', $width = null, $height = null): string
    {
        $image = str_replace('\\', '/', $image);
        if ($image[0] != '/') {
            $image = '/' . $image;
        }
        $file = $_SERVER['DOCUMENT_ROOT'] . $image;
        $base64 = 'data:image/png;base64,' . base64_encode(file_get_contents($file));
        if (is_null($width) && is_null($height)) {
            return "<img src='$base64' alt='$alt'/>";
        } elseif (is_null($width)) {
            return "<img src='$base64' alt='$alt' height='$height'/>";
        } elseif (is_null($height)) {
            return "<img src='$base64' alt='$alt' width='$width'/>";
        } else {
            return "<img src='$base64' alt='$alt' width='$width' height='$height'/>";
        }
    }
}