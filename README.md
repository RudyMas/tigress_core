# Tigress Core

The Core module of the Tigress Framework

## Installation

You can create a new Tigress project by using composer.

````
composer create-project tigress/tigress <project_name>
````

## Documentation

### Core Module (Class: `Tigress\Core`)

The Core module provides the essential functionalities required for building applications using the Tigress Framework.
It includes components for routing, request handling, response generation, and middleware support.

#### Configuration files

- **config/config.php**: This file contains the main configuration settings for the application, including database
  connections, caching options, and other global settings.
- **config/routes.php**: This file defines the routing rules for the application, mapping URLs to specific controllers
  and actions.
- **system/config.php**: This file contains system-level configuration settings that are essential for the framework's
  operation.

More information about these files can be found in the README.md file located in the tigress/tigress package.

#### Constants used containing configurations

- **CONFIG**: Contains the configuration settings for the application. File located at `config/config.php`.
- **SYSTEM**: Contains the system configuration settings for the Tigress Framework. File located at `system/config.php`.
- **BASE_URL**: The base URL of the application → used for generating absolute URLs.
- **SYSTEM_ROOT**: The root directory of the Tigress Framework installation.
- **WEBSITE**: The website name as defined in the configuration file.
- **SERVER_TYPE**: The type of server the application is running on (e.g., development, production or test).

#### Constants used containing Classes

- **TRANSLATIONS**: The helper class for configuring and managing translations in the application.
- **TWIG**: The Twig templating engine instance used for rendering views.
- **MENU**: The menu manager instance used for handling navigation menus in the application.
- **SECURITY**: The security manager instance used for handling authentication and authorization.
- **RIGHTS**: The rights manager instance used for managing user permissions.
- **ROUTER**: The router instance used for handling HTTP requests and routing them to the appropriate controllers.
- **DATABASE**: The database connection instance used for interacting with the database.

#### Functions that can be used

- debug(mixed \$data, bool \$stop = true): void  
  Outputs debug information about the provided data. If \$stop is true, it stops the execution of the script after
  displaying the debug information.

---

### DisplayHelper Module (Class: `Tigress\DisplayHelper`)

The DisplayHelper module provides utility functions for rendering views and managing templates in the Tigress Framework.
If running on a development server, it puts the Twig environment in debug mode.

#### Functions that can be used

- **addGlobal**(string \$name, mixed \$value): void   
  Adds a global variable to the Twig environment.


- addPath(string \$path): void  
  Adds a new path to the Twig loader to look for templates.


- render(\?string \$template, array \$data = [], string \$type = 'TWIG', int \$httpResponseCode = 200, array
  \$config = []): string    
  Renders a template with the provided data and returns the rendered content as a string based on the specified type (
  TWIG, PHP, or JSON).
    - **\$template**: The name of the template to render.
    - **\$data**: An associative array of data to pass to the template.
    - **\$type**: The type of template to render (HTML, JSON, DT, PDF, PHP, TWIG, STWIG or XML).
    - **\$httpResponseCode**: The HTTP response code to set for the response.
    - **\$config**: Additional configuration options for rendering.


- redirect(string \$url): void  
  Redirects the user to the specified URL. This can be an internal or external URL.

#### Data available in all Twig templates

- **_COOKIE**: The current COOKIE request data.
- **_GET**: The current GET request data.
- **_POST**: The current POST request data.
- **_SESSION**: The current session data.
- **BASE_URL**: The base URL of the application.
- **SERVER_TYPE**: The type of server the application is running on (e.g., development, production or test).
- **SYSTEM_ROOT**: The root directory of the Tigress Framework installation.
- **WEBSITE**: The website information as defined in the configuration file.
- **menu**: The menu manager instance used for handling navigation menus in the application.
- **rights**: Array of the rights of the user. (access, read, write and delete).

#### Extra added Twig filters

- **base64_encode**: Encodes a string using Base64 encoding.
- **bitwise_and**: Performs a bitwise AND operation between two integers.
- **bitwise_or**: Performs a bitwise OR operation between two integers.
- **bitwise_xor**: Performs a bitwise XOR operation between two integers.
- **bitwise_not**: Performs a bitwise NOT operation on an integer.

#### Extra added Twig functions

- **__**: Returns the translated string for the given key using the TRANSLATIONS class.
- **add_slider**: Renders a slider component with the specified parameters.
- **file_exists**: Checks if a file exists at the given path.
- **get_all_attrs**: Returns a string of all attributes for an HTML element.
- **get_attr**: Returns the value of a specific attribute for an HTML element.
- **in_keys**: Checks if a value exists in the keys of an array.
- **in_values**: Checks if a value exists in the values of an array.
- **match**: Performs a regular expression match on a string.
- **strip_dangerous_tags**: Strips dangerous HTML tags from a string to prevent XSS attacks.
- **week_range**: Returns the start and end dates of the week for a given date.

---

### LoggerHelper Module (Class: `Tigress\LoggerHelper`)

The LoggerHelper module provides logging functionalities for the Tigress Framework. It allows you to log messages at
different levels (info, warning, error) and manage log files.

#### Functions that can be used

- create(string \$channelName, Level \$level = Level::Error, int \$retentionDays = 30, ?string \$dateFormat = 'Y-m-d',
  ?string \$logDirectory = null): LoggerInterface  
  Creates and returns a logger instance with the specified channel name, log level, retention days, date format, and
  log directory.
    - **\$channelName**: The name of the logging channel.
    - **\$level**: The minimum log level to record (default is Error).
    - **\$retentionDays**: The number of days to retain log files (default is 30).
    - **\$dateFormat**: The date format for log file names (default is 'Y-m-d').
    - **\$logDirectory**: The directory where log files will be stored (default is 'SYSTEM_ROOTS/logs').


- getDefault(): LoggerInterface  
  Returns the default logger instance with the channel name 'tigress'.

---

### PdfCreatorHelper Module (Class: `Tigress\PdfCreatorHelper`)

The PdfCreatorHelper module provides functionalities for generating PDF documents using the Dompdf library in the
Tigress
Framework. It allows you to create PDF files from HTML content with various configuration options.

It can also be used as a standalone library.

#### Functions that can be used

- createPdf(string \$html, string \$format = 'A4', string \$orientation = 'portrait', string \$filename = '
  document.pdf', string \$filepath = '/public/tmp/', bool \$pagination = false, int \$attachment = 1): void  
  Creates a PDF document from the provided HTML content and creates a stream of file depending.
    - **\$html**: The HTML content to be converted into a PDF.
    - **\$format**: The page format for the PDF (default is 'A4').
    - **\$orientation**: The page orientation for the PDF (default is 'portrait').
    - **\$filename**: The name of the generated PDF file (default is 'document.pdf').
    - **\$filepath**: The directory where the PDF file will be saved (default is '/public/tmp/').
    - **\$pagination**: Whether to include pagination in the PDF (default is false).
    - **\$attachment**: The attachment disposition for the PDF (2 for saving the file on the server, 1 for download, 0
      for inline display; default is 1).


- getImage(string \$image, string $alt = 'Logo', ?array $options = null): string  
  Returns img-tag with the base64 encoded string of an image to be used in the PDF.
    - **\$image**: The path to the image file.
    - **\$alt**: The alt text for the image (default is 'Logo').
    - **\$options**: An associative array of options for the image (e.g., width and/or height).


- setLanguage(string $language): void  
  Sets the language for the PDF document.
    - **\$language**: The language code (e.g., 'en', 'fr', 'de').

---

### TranslationHelper Module (Class: `Tigress\TranslationHelper`)

The TranslationHelper module provides functionalities for managing translations and localization in the Tigress
Framework. It allows you to load translation files, retrieve translated strings, and handle multiple languages.

#### Functions that can be used

- load(string \$filePath): void  
  Loads a translation file from the specified file path and adds its translations to the system.
    - **\$filePath**: The path to the translation file.

No other functionality is needed because the Tigress Core provides the __('...') function for translations in Twig
templates, PHP files and JavaScript files.

---

### Build in Routes
The Tigress Core module comes with several built-in routes.  
- **/phpinfo**: Displays the PHP info page. (Only available for superadmins).
- **/version**: Displays the current version of the Tigress Framework and the loaded modules.
- **/help**: Displays the Tigress Framework Documentation page. (Not yet available).