<?php

namespace Controller\Core;

use Tigress\Core;

/**
 * Class ApiController - This class is used to handle API requests (PHP version 8.3)
 *
 * @author Rudy Mas <rudy.mas@rudymas.be>
 * @copyright 2024 Rudy Mas (https://rudymas.be)
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License, version 3 (GPL-3.0)
 * @version 0.9.0
 * @package Controller\Core
 */
class ApiController
{
    /**
     * @var Core
     */
    private Core $Core;

    /**
     * @param Core $Core
     */
    public function __construct(Core $Core)
    {
        $this->Core = $Core;
    }

    /**
     * Get Data from Database / Table
     *
     * @return void
     */
    public function getData(): void
    {
        print 'Hello from ApiController - getData()';
    }

    /**
     * Post Data to Database / Table
     *
     * @return void
     */
    public function postData(): void
    {
        print 'Hello from ApiController - postData()';
    }

    /**
     * Put Data to Database / Table
     *
     * @return void
     */
    public function putData(): void
    {
        print 'Hello from ApiController - putData()';
    }
}