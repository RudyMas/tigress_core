<?php

namespace Tigress;

use DOMDocument;
use Exception;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

/**
 * Class DisplayHelper (PHP version 8.3)
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 1.1.1
 * @lastmodified 2024-07-03
 * @package Tigress
 */
class DisplayHelper
{
    private FilesystemLoader $loader;
    private Environment $twig;

    /**
     * @param string $viewFolder
     * @param bool $debug
     */
    public function __construct(string $viewFolder = __DIR__ . '/../view/', bool $debug = false)
    {
        // Setting up TWIG for templating
        $this->loader = new FilesystemLoader($viewFolder);
        $this->twig = new Environment($this->loader, ['debug' => $debug]);
        if ($debug) $this->twig->addExtension(new DebugExtension());
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
     * @param string|null $template
     * @param array $data
     * @param string $type
     * @param int $httpResponseCode
     * @return void
     * @throws Exception
     */
    public function render(
        ?string $template,
        array   $data = [],
        string  $type = 'TWIG',
        int     $httpResponseCode = 200
    ): void
    {
        $mergedData = array_merge($data, [
            'BASE_URL' => BASE_URL,
            'SYSTEM_ROOT' => SYSTEM_ROOT,
            'WEBSITE' => WEBSITE,
        ]);

        switch (strtoupper($type)) {
            case 'TWIG':
                $this->renderTwig($template, $mergedData);
                break;
            case 'HTML':
                $this->renderHtml($template);
            default:
                throw new Exception("<p><b>Exception:</b> Wrong page type ({$type}) given.</p>", 500);
        }
    }

    /**
     * Show a HTML file
     *
     * @param $template
     * @return void
     */
    public function renderHtml($template): void
    {
        $display = $_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/src/Views/' . $template;
        if (file_exists($display)) {
            readfile($display);
        } else {
            header('HTTP/1.1 404 Not Found', true, 404);
        }
    }

    /**
     * Show a TWIG template
     *
     * @param string $template
     * @param array $data
     * @return void
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderTwig(string $template, array $data = []): void
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