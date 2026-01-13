<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;

class BookService
{
    use ConsumesExternalService;

    /**
     * The base uri to be used to consume the books service
     * @var string
     */
    public $baseUri;

    public function __construct()
    {
        $this->baseUri = env('BOOKS_SERVICE_BASE_URL');

        // Validate configuration
        if (empty($this->baseUri)) {
            throw new \RuntimeException('BOOKS_SERVICE_BASE_URL is not configured in .env file');
        }
    }

    /**
     * Get a single book from the books service
     * @return array
     */
    public function obtainBook($bookId)
    {
        return $this->performRequest('GET', "/books/{$bookId}");
    }
}