<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;

class UserService
{
    use ConsumesExternalService;

    /**
     * The base uri to be used to consume the users service
     * @var string
     */
    public $baseUri;

    public function __construct()
    {
        $this->baseUri = env('USERS_SERVICE_BASE_URL');

        // Validate configuration
        if (empty($this->baseUri)) {
            throw new \RuntimeException('USERS_SERVICE_BASE_URL is not configured in .env file');
        }
    }

    /**
     * Get a single user from the users service
     * @return array
     */
    public function obtainUser($userId)
    {
        return $this->performRequest('GET', "/authors/{$userId}");
    }
}