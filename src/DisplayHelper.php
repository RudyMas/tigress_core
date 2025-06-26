<?php

namespace Tigress;

use Controller\Menu;
use DOMDocument;
use Exception;
use HTMLPurifier;
use HTMLPurifier_Config;
use JetBrains\PhpStorm\NoReturn;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\DebugExtension;
use Twig\Extra\Intl\IntlExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Class DisplayHelper (PHP version 8.4)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024-2025 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 2025.06.26.3
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
        return '2025.06.26';
    }

    /**
     * @param string $viewFolder
     * @param bool $debug
     */
    public function __construct(string $viewFolder = __DIR__ . '/views/', bool $debug = false)
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

        $file = SYSTEM_ROOT . '/vendor/tigress/core/translations/base_' . substr(CONFIG->website->html_lang, 0, 2) . '.json';
        if (!file_exists($file)) {
            $file = SYSTEM_ROOT . '/vendor/tigress/core/translations/base_en.json';
        }
        $translations = json_decode(file_get_contents($file), true);
        $this->twig->addGlobal('base_trans', $translations);

        // Register custom filters in Twig
        $this->twig->addFilter(new TwigFilter('bitwise_and', function ($a, $b): int {
            return $a & $b;
        }));
        $this->twig->addFilter(new TwigFilter('bitwise_or', function ($a, $b): int {
            return $a | $b;
        }));
        $this->twig->addFilter(new TwigFilter('bitwise_xor', function ($a, $b): int {
            return $a ^ $b;
        }));
        $this->twig->addFilter(new TwigFilter('bitwise_not', function ($a): int {
            return ~$a;
        }));
        $this->twig->addFilter(new TwigFilter('base64_encode', function ($data): string {
            return base64_encode($data);
        }));

        // Register custom functions in Twig
        $this->twig->addFunction(new TwigFunction('in_keys', function ($needle, $haystack, $strict = false): bool {
            return in_array($needle, array_keys($haystack), $strict);
        }));
        $this->twig->addFunction(new TwigFunction('in_values', function ($needle, $haystack, $strict = false): bool {
            return in_array($needle, array_values($haystack), $strict);
        }));
        $this->twig->addFunction(new TwigFunction('trans', function ($key, $translations): string {
            $lang = CONFIG->website->html_lang ?? 'en';
            $lang = substr($lang, 0, 2);
            return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
        }));
        $this->twig->addFunction(new TwigFunction('match', function (string $pattern, string $subject): array {
            if (!preg_match($pattern, $subject, $matches)) {
                return ['no_match' => true];
            }
            return $matches;
        }));
        $this->twig->addFunction(new TwigFunction('get_attr', function (string $html, string $attr): ?string {
            if (preg_match('/' . preg_quote($attr, '/') . '\s*=\s*"([^"]+)"/i', $html, $matches)) {
                return $matches[1];
            }
            return null;
        }));
        $this->twig->addFunction(new TwigFunction('get_all_attrs', function (string $input): array {
            $attributes = [];

            preg_match_all('/(\w+)\s*=\s*"([^"]*)"/', $input, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $attributes[$match[1]] = $match[2];
            }

            return $attributes;
        }));
        $this->twig->addFunction(new TwigFunction('week_range', function (string $isoWeek): string {
            if (!preg_match('/^(\d{4})-W(\d{2})$/', $isoWeek, $m)) {
                return 'Ongeldige weeknotatie';
            }

            $year = (int)$m[1];
            $week = (int)$m[2];

            $start = new \DateTime();
            $start->setISODate($year, $week);

            $end = clone $start;
            $end->modify('+6 days');

            return sprintf(
                '%d - %s tem %s',
                $week,
                $start->format('d-m-Y'),
                $end->format('d-m-Y')
            );
        }));
        $this->twig->addFunction(new TwigFunction('file_exists', function (string $path): bool {
            $fullPath = SYSTEM_ROOT . $path;
            return file_exists($fullPath);
        }));

        $purifiers = [];
        $this->twig->addFunction(new TwigFunction('strip_dangerous_tags', function ($text, $profile = 'default') use (&$purifiers): string {
            if (!isset($purifiers[$profile])) {
                $config = HTMLPurifier_Config::createDefault();
                match ($profile) {
                    'links' => $config->set('HTML.Allowed', 'b,i,u,a[href],br'),
                    'images' => $config->set('HTML.Allowed', 'b,i,u,img[src|alt|width|height],br'),
                    default => $config->set('HTML.Allowed', 'b,i,u,br'),
                };

                $purifiers[$profile] = new HTMLPurifier($config);
            }

            return $purifiers[$profile]->purify($text);
        }));
    }

    /**
     * Add a global variable to Twig
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function addGlobal(string $name, mixed $value): void
    {
        $this->twig->addGlobal($name, $value);
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
                if (ob_get_level() > 0) ob_flush();
                flush();
                break;
            case 'JSON':
                $this->renderJson($data, $httpResponseCode, $config);
                if (ob_get_level() > 0) ob_flush();
                flush();
                break;
            case 'DT':
                $this->renderDatatable($data, $httpResponseCode);
                if (ob_get_level() > 0) ob_flush();
                flush();
                break;
            case 'PDF':
                $this->renderPDF($template, $data, $config);
                break;
            case 'PHP':
                $this->renderPhp($template, $data);
                break;
            case 'TWIG':
                $this->renderTwig($template, $data);
                if (ob_get_level() > 0) ob_flush();
                flush();
                break;
            case 'STWIG':
                return $this->renderTwigString($template, $data);
            case 'XML':
                $this->renderXml($data, $httpResponseCode, $config);
                if (ob_get_level() > 0) ob_flush();
                flush();
                break;
            default:
                throw new Exception("<p><b>Exception:</b> Wrong page type ({$type}) given.</p>", 500);
        }
        return '';
    }

    /**
     * Redirect the user to another page
     *
     * @param string $page Page to redirect to (Can be a URL or a routing directive)
     */
    #[NoReturn] public function redirect(string $page): void
    {
        if (in_array(SERVER_TYPE, ['development', 'test']) && headers_sent($file, $line)) {
            echo "<pre style='color:red; background:#ffecec; padding:10px; border:1px solid #f00'>";
            echo "⚠️ DEBUG: Redirect naar $page kon niet meer, headers zijn al verzonden in $file op lijn $line";
            echo "</pre>";
            exit;
        }

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
     * @param array $config
     * @return void
     */
    private function renderJson(array $data, int $httpResponseCode = 200, array $config = []): void
    {
        $convert = $this->checkHttpResponseCode($httpResponseCode, $data);
        $convert->arrayToJson();

        http_response_code($httpResponseCode);
        if (isset($config['filename'])) {
            header('Content-Disposition: attachment; filename="' . $config['filename'] . '"');
        }
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
     * Config options:
     * - xmlRoot: The root element of the XML (default: root)
     * - prevKey: The key of the previous element (default: data)
     * - filename: The name of the XML file (default: null)
     *
     * @param array $data
     * @param int $httpResponseCode
     * @param array $config
     * @return void
     * @throws Exception
     */
    private function renderXml(array $data, int $httpResponseCode = 200, array $config = []): void
    {
        $xmlRoot = $config['xmlRoot'] ?? 'root';
        $prevKey = $config['prevKey'] ?? 'data';

        $convert = $this->checkHttpResponseCode($httpResponseCode, $data);
        $convert->arrayToXml($xmlRoot, $prevKey);

        http_response_code($httpResponseCode);
        if (isset($config['filename'])) {
            header('Content-Disposition: attachment; filename="' . $config['filename'] . '"');
        }
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
            'attachment' => 1,
            'language' => 'nl',
        ], $pdfConfig);
        $html = $this->twig->render($template, $data);
        $html = $this->ImgTagToBase64InHtml($html);

        $pdf->setLanguage($pdfConfig['language']);

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
        $dom->loadHTML($html, LIBXML_NOERROR);

        $images = $dom->getElementsByTagName('img');
        foreach ($images as $image) {
            $src = $image->getAttribute('src');
            $src = preg_replace('#https?://[^/]+#', '', $src);
            $src = preg_replace('/\.\.\//', '/', $src);
            $src = preg_replace('/%20/', ' ', $src);
            $type = pathinfo($src, PATHINFO_EXTENSION);
            if (is_file(SYSTEM_ROOT . $src)) {
                $width = $image->getAttribute('width');
                $height = $image->getAttribute('height');

                $data = file_get_contents(SYSTEM_ROOT . $src);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $image->setAttribute('src', $base64);

                # make sure the image is not too big
                if ($width && $height) {
                    $image->setAttribute('width', $width);
                    $image->setAttribute('height', $height);
                } elseif ($width && !$height) {
                    $image->setAttribute('width', $width);
                    $image->setAttribute('height', 'auto');
                } elseif (!$width && $height) {
                    $image->setAttribute('width', 'auto');
                    $image->setAttribute('height', $width);
                } else {
                    $image->setAttribute('width', '100%');
                    $image->setAttribute('height', 'auto');
                }
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