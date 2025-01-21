<?php

namespace Tigress;

use Controller\Menu;
use DOMDocument;
use Exception;
use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;

/**
 * Class DisplayHelper (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024-2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.01.21.1
 * @package Tigress\DisplayHelper
 */
class DisplayHelper
{
    private FilesystemLoader $loader;
    private Environment $twig;

    /**
     * Get the version of the DisplayHelper
     *
     * @return string
     */
    public static function version(): string
    {
        return '2025.01.21';
    }

    /**
     * @param string $viewFolder
     * @param bool $debug
     */
    public function __construct(string $viewFolder = __DIR__ . '/../view/', bool $debug = false)
    {
        // Active error reporting PHP
        if ($debug) {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        }

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
     * @param array $config
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function render(
        ?string $template,
        array   $data = [],
        string  $type = 'TWIG',
        int     $httpResponseCode = 200,
        array   $config = [],
    ): string
    {
        switch (strtoupper($type)) {
            case 'HTML':
                $this->renderHtml($template);
                break;
            case 'JSON':
                $this->renderJson($data, $httpResponseCode);
                break;
            case 'DT':
                $this->renderDatatable($data, $httpResponseCode);
                break;
            case 'PDF':
                $this->renderPDF($template, $data, $config);
                break;
            case 'PHP':
                $this->renderPhp($template, $data);
                break;
            case 'TWIG':
                $this->renderTwig($template, $data);
                break;
            case 'STWIG':
                return $this->renderTwigString($template, $data);
                break;
            case 'XML':
                $this->renderXml($data, $httpResponseCode, $config);
                break;
            default:
                throw new Exception("<p><b>Exception:</b> Wrong page type ({$type}) given.</p>", 500);
        }
        ob_flush();
        flush();
        return '';
    }

    /**
     * Redirect the user to another page
     *
     * @param string $page Page to redirect to (Can be a URL or a routing directive)
     */
    #[NoReturn] public function redirect(string $page): void
    {
        if (preg_match("/(http|ftp|https)?:?\/\//", $page)) {
            header('Location: ' . $page);
        } else {
            if (!empty(SYSTEM->subfolder)) {
                header('Location: /' . SYSTEM->subfolder . $page);
            } else {
                header('Location: ' . $page);
            }
        }
        exit;
    }

    /**
     * Show an HTML file
     *
     * @param $template
     * @return void
     */
    private function renderHtml($template): void
    {
        $display = $_SERVER['DOCUMENT_ROOT'] . BASE_URL . '/src/Views/' . $template;
        if (file_exists($display)) {
            readfile($display);
        } else {
            header('HTTP/1.1 404 Not Found', true, 404);
        }
    }

    /**
     * Show a JSON output
     *
     * @param array $data
     * @param int $httpResponseCode
     * @return void
     */
    private function renderJson(array $data, int $httpResponseCode = 200): void
    {
        $convert = $this->checkHttpResponseCode($httpResponseCode, $data);
        $convert->arrayToJson();

        http_response_code($httpResponseCode);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        print($convert->getJsonData());
    }

    /**
     * Return a JSON output for a Datatable
     *
     * @param array $data
     * @param int $httpResponseCode
     * @return void
     */
    private function renderDatatable(array $data, int $httpResponseCode = 200): void
    {
        $convert = $this->checkHttpResponseCode($httpResponseCode, ['data' => $data]);
        $convert->arrayToJson();

        http_response_code($httpResponseCode);
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        print($convert->getJsonData());
    }

    /**
     * Show a PHP file
     *
     * @param string $template
     * @param array $data
     * @return void
     */
    private function renderPhp(string $template, array $data = []): void
    {
        extract($data);
        include_once $template;
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
    private function renderTwig(string $template, array $data = []): void
    {
        $mergedData = $this->prepareTwigOutput($data);
        echo $this->twig->render($template, $mergedData);

        // Clear the session messages
        if (isset($_SESSION['message'])) {
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['warning'])) {
            unset($_SESSION['warning']);
        }
        if (isset($_SESSION['success'])) {
            unset($_SESSION['success']);
        }
    }

    /**
     * Render a TWIG template as a string
     *
     * @param string $template
     * @param array $data
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    private function renderTwigString(string $template, array $data = []): string
    {
        $mergedData = $this->prepareTwigOutput($data);
        return $this->twig->render($template, $mergedData);
    }

    /**
     * Show an XML output
     *
     * @param array $data
     * @param int $httpResponseCode
     * @param array $config
     * @return void
     */
    private function renderXml(array $data, int $httpResponseCode = 200, array $config = []): void
    {
        $xmlRoot = $config['xmlRoot'] ?? 'root';
        $xmlItem = $config['xmlItem'] ?? 'item';

        $convert = $this->checkHttpResponseCode($httpResponseCode, $data);
        $convert->arrayToXml($xmlRoot, $xmlItem);

        http_response_code($httpResponseCode);
        header('Content-Type: application/xml');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: *');
        print($convert->getXmlData());
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
    private function renderPDF(string $template, array $data = [], array $pdfConfig = []): void
    {
        $pdf = new PdfCreatorHelper();

        $pdfStyle = file_get_contents(SYSTEM_ROOT . '/vendor/tigress/core/public/css/PdfCreatorCss.css');
        $data = array_merge($data, ['pdfStyle' => $pdfStyle]);

        $pdfConfig = array_merge([
            'format' => 'A4',
            'orientation' => 'portrait',
            'filename' => 'document.pdf',
            'filepath' => '/public/tmp/',
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
            $pdfConfig['filepath'],
            $pdfConfig['pagination'],
            $pdfConfig['attachment']
        );
    }

    /**
     * Check the HTTP response code and return the data
     *
     * @param int $httpResponseCode
     * @param array $data
     * @return DataConverter
     */
    private function checkHttpResponseCode(int $httpResponseCode, array $data): DataConverter
    {
        if ($httpResponseCode >= 200 && $httpResponseCode < 300) {
            $outputData = $data;
        } else {
            $outputData['error']['code'] = $httpResponseCode;
            $outputData['error']['message'] = 'Error ' . $httpResponseCode . ' has occurred';
        }

        $convert = new DataConverter();
        $convert->setArrayData($outputData);
        return $convert;
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
            $src = preg_replace('/\.\.\//', '/', $src);
            $src = preg_replace('/%20/', ' ', $src);
            $type = pathinfo($src, PATHINFO_EXTENSION);
            $data = file_get_contents(SYSTEM_ROOT . $src);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            $image->setAttribute('src', $base64);

            $height = $image->getAttribute('height');
            $width = $image->getAttribute('width');

            # make sure the image is not too big
            if ($height && $width) {
                $image->setAttribute('style', 'max-width: ' . $width . '; height: ' . $height . ';');
            } elseif ($height && !$width) {
                $image->setAttribute('style', 'max-width: auto; height: ' . $height . ';');
            } elseif (!$height && $width) {
                $image->setAttribute('style', 'max-width: ' . $width . '; height: auto;');
            } else {
                $image->setAttribute('style', 'max-width: 100%; height: auto;');
            }
        }

        return $dom->saveHTML();
    }

    /**
     * @param array $data
     * @return array|array[]|Menu[]|string[]
     */
    private function prepareTwigOutput(array $data): array
    {
        if (isset($_SESSION['user'])) {
            $rights = [
                'access' => RIGHTS->checkRights('access'),
                'read' => RIGHTS->checkRights('read'),
                'write' => RIGHTS->checkRights('write'),
                'delete' => RIGHTS->checkRights('delete'),
            ];
        } else {
            $rights = [
                'access' => false,
                'read' => false,
                'write' => false,
                'delete' => false,
            ];
        }

        return array_merge($data, [
            '_SESSION' => $_SESSION,
            '_POST' => $_POST,
            '_GET' => $_GET,
            'BASE_URL' => BASE_URL,
            'SERVER_TYPE' => SERVER_TYPE,
            'SYSTEM_ROOT' => SYSTEM_ROOT,
            'WEBSITE' => WEBSITE,
            'menu' => MENU,
            'rights' => $rights,
        ]);
    }
}