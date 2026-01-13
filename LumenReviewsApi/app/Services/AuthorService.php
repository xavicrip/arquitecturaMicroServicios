<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;

class AuthorService
{
    use ConsumesExternalService;

    /**
     * The base uri to consume the Authors service
     * @var string
     */
    public $baseUri;

    /**
     * Authorization secret to pass to Author service
     * @var string
     */
    public $secret;

    public function __construct()
    {
        $this->baseUri = config('services.authors.base_uri');
        $this->secret = config('services.authors.secret');
    }

    /**
     * Obtain the full list of authors from the authors service
     * @return string
     */
    public function obtainAuthors()
    {
        return $this->performRequest('GET', '/authors');
    }

    /**
     * Obtain one single author from the authors service
     * @param  int $authorId
     * @return string
     */
    public function obtainAuthor($authorId)
    {
        return $this->performRequest('GET', "/authors/{$authorId}");
    }
}