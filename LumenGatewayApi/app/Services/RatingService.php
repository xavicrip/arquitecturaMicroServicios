<?php

namespace App\Services;

use App\Traits\ConsumesExternalService;

class RatingService
{
    use ConsumesExternalService;

    /**
     * The base uri to be used to consume the ratings service
     * @var string
     */
    public $baseUri;

    /**
     * The secret to be used to consume the ratings service
     * @var string
     */
    public $secret;

    public function __construct()
    {
        $this->baseUri = config('services.ratings.base_uri');
        $this->secret = config('services.ratings.secret');

        // Validate configuration
        if (empty($this->baseUri)) {
            throw new \RuntimeException('RATINGS_SERVICE_BASE_URL is not configured in .env file');
        }
    }

    /**
     * Get the full list of ratings from the ratings service
     * @return string
     */
    public function obtainRatings()
    {
        return $this->performRequest('GET', '/ratings');
    }

    /**
     * Create an instance of rating using the ratings service
     * @return string
     */
    public function createRating($data)
    {
        return $this->performRequest('POST', '/ratings', $data);
    }

    /**
     * Get a single rating from the ratings service
     * @return string
     */
    public function obtainRating($rating)
    {
        return $this->performRequest('GET', "/ratings/{$rating}");
    }

    /**
     * Edit a single rating from the ratings service
     * @return string
     */
    public function editRating($data, $rating)
    {
        return $this->performRequest('PUT', "/ratings/{$rating}", $data);
    }

    /**
     * Remove a single rating from the ratings service
     * @return string
     */
    public function deleteRating($rating)
    {
        return $this->performRequest('DELETE', "/ratings/{$rating}");
    }

    /**
     * Get all ratings for a specific book from the ratings service
     * @return string
     */
    public function obtainRatingsByBook($bookId)
    {
        return $this->performRequest('GET', "/ratings/book/{$bookId}");
    }

    /**
     * Get average rating for a specific book from the ratings service
     * @return string
     */
    public function obtainAverageRating($bookId)
    {
        return $this->performRequest('GET', "/ratings/book/{$bookId}/average");
    }

}