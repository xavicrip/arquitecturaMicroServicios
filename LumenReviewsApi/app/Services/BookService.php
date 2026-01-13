<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;

class BookService
{
    use ConsumesExternalService;

    /**
     * The base uri to consume the Books service
     * @var string
     */
    public $baseUri;

    /**
     * Authorization secret to pass to Book service
     * @var string
     */
    public $secret;

    public function __construct()
    {
        $this->baseUri = config('services.books.base_uri');
        $this->secret = config('services.books.secret');
    }

    /**
     * Obtain the full list of books from the books service
     * @return string
     */
    public function obtainBooks()
    {
        return $this->performRequest('GET', '/books');
    }

    /**
     * Obtain one single book from the books service
     * @param  int $bookId
     * @return string
     */
    public function obtainBook($bookId)
    {
        return $this->performRequest('GET', "/books/{$bookId}");
    }
}