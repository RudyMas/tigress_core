<?php

namespace Tigress;

use DOMDocument;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extra\Intl\IntlExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

/**
 * Class TwigClass - A simple wrapper around Twig
 * - Includes the IntlExtension
 * - Includes the DebugExtension (if debug is set to true)
 * - Includes a method to add a path to the loader
 * - Includes a method to render a template
 * - Includes a method to redirect the user to another page
 * - Includes a method to get the data to use in the menu
 * - Includes a method to transfer a standard img-tag to a base64 encoded img-tag
 * - Includes a method to create & download a PDF file from a template
 * - Includes a method to dump a variable
 *
 * Added Filters:
 * - bitwise_and: Perform a bitwise AND operation
 * - bitwise_or: Perform a bitwise OR operation
 * - bitwise_xor: Perform a bitwise XOR operation
 * - bitwise_not: Perform a bitwise NOT operation
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 1.0.0
 * @package Tigress
 * @see https://twig.symfony.com/doc/3.x/api.html
 * @see https://twig.symfony.com/doc/3.x/api.html#environment-options
 * @see https://twig.symfony.com/doc/3.x/api.html#debugging
 */
class TwigHelper
{
    private FilesystemLoader $loader;
    private Environment $twig;

    /**
     * @param string $viewFolder
     * @param bool $debug
     */
    public function __construct(string $viewFolder = __DIR__ . '/../view/', bool $debug = false)
    {
        $this->loader = new FilesystemLoader($viewFolder);
        $this->twig = new Environment($this->loader, ['debug' => $debug]);
        $this->twig->addExtension(new IntlExtension());

        // Register custom filters in Twig
        $this->twig->addFilter(new TwigFilter('bitwise_and', function ($a, $b) {
            return $a & $b;
        }));

        $this->twig->addFilter(new TwigFilter('bitwise_or', function ($a, $b) {
            return $a | $b;
        }));

        $this->twig->addFilter(new TwigFilter('bitwise_xor', function ($a, $b) {
            return $a ^ $b;
        }));

        $this->twig->addFilter(new TwigFilter('bitwise_not', function ($a) {
            return ~$a;
        }));
    }

    /**
     * Add a path to the loader
     *
     * @param string $path
     * @return void
     * @throws LoaderError
     */
    public function addPath(string $path): void
    {
        $this->loader->addPath($path);
    }

    /**
     * Render a template
     *
     * @param string $template
     * @param array $data
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function render(string $template, array $data = []): void
    {
        echo $this->twig->render($template, $data);
    }

    /**
     * Create a PDF file from a template and download it
     *
     * You can use following options in the $pdfConfig array:
     * - format: The format of the PDF (default: A4)
     * - orientation: The orientation of the PDF (default: portrait)
     * - filename: The name of the PDF file (default: document.pdf)
     * - pagination: Show pagination (default: false)
     * - attachment: 1 = download, 0 = open in browser (default: 1)
     *
     * @param string $template
     * @param array $data
     * @param array $pdfConfig
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function createPDF(string $template, array $data = [], array $pdfConfig = []): void
    {
        $pdf = new PdfCreatorHelper();

        $pdfStyle = file_get_contents('../src/helper/css/PdfCreatorCss.css');
        $data = array_merge($data, ['pdfStyle' => $pdfStyle]);

        $pdfConfig = array_merge([
            'format' => 'A4',
            'orientation' => 'portrait',
            'filename' => 'document.pdf',
            'pagination' => false,
            'attachment' => 1
        ], $pdfConfig);
        $html = $this->twig->render($template, $data);
        $html = $this->ImgTagToBase64InHtml($html);

        $pdf->createPdf(
            $html,
            $pdfConfig['format'],
            $pdfConfig['orientation'],
            $pdfConfig['filename'],
            $pdfConfig['pagination'],
            $pdfConfig['attachment']
        );
    }

    /**
     * Redirect the user to another page
     *
     * @param string $page Page to redirect to (Can be a URL or a routing directive)
     */
    public function redirect(string $page): void
    {
        if (preg_match("/(http|ftp|https)?:?\/\//", $page)) {
            header('Location: ' . $page);
        } else {
            $dirname = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
            header('Location: ' . $dirname . $page);
        }
        exit;
    }

    /**
     * Transfer a standard img-tag to a base64 encoded img-tag
     *
     * @param string $html
     * @return string
     */
    private function ImgTagToBase64InHtml(string $html): string
    {
        $dom = new DOMDocument();
        $dom->loadHTML($html);
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            $src = preg_replace('/%20/', ' ', $src);
            $type = pathinfo($src, PATHINFO_EXTENSION);
            $data = file_get_contents($src);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $image->setAttribute('src', $base64);

            # make sure the image is not too big
            $image->setAttribute('style', 'max-width: 100%; height: auto;');
        }

        return $dom->saveHTML();
    }
}