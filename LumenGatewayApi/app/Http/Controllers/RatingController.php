<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Services\RatingService;
use Illuminate\Http\Response;
use App\Services\BookService;
use App\Services\AuthorService;

class RatingController extends Controller
{
    use ApiResponser;

    /**
     * The service to consume the rating service
     * @var RatingService
     */
    public $ratingService;

    /**
     * The service to consume the book service
     * @var BookService
     */
    public $bookService;

    /**
     * The service to consume the author service
     * @var AuthorService
     */
    public $authorService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(RatingService $ratingService, BookService $bookService, AuthorService $authorService)
    {
        $this->ratingService = $ratingService;
        $this->bookService = $bookService;
        $this->authorService = $authorService;
    }

    /**
     * Retrieve and show all the Ratings
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        return $this->successResponse($this->ratingService->obtainRatings());
    }

    /**
     * Creates an instance of Rating
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validate book exists
        $this->bookService->obtainBook($request->book_id);

        // Validate user exists
        $this->authorService->obtainAuthor($request->user_id);

        return $this->successResponse($this->ratingService->createRating($request->all()), Response::HTTP_CREATED);
    }

    /**
     * Obtain and show an instance of Rating
     * @return Illuminate\Http\Response
     */
    public function show($rating)
    {
        return $this->successResponse($this->ratingService->obtainRating($rating));
    }

    /**
     * Updated an instance of Rating
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $rating)
    {
        // Validate book_id if provided
        if ($request->has('book_id')) {
            $this->bookService->obtainBook($request->book_id);
        }

        // Validate user_id if provided
        if ($request->has('user_id')) {
            $this->authorService->obtainAuthor($request->user_id);
        }

        return $this->successResponse($this->ratingService->editRating($request->all(), $rating));
    }

    /**
     * Removes an instance of Rating
     * @return Illuminate\Http\Response
     */
    public function destroy($rating)
    {
        return $this->successResponse($this->ratingService->deleteRating($rating));
    }

    /**
     * Get all ratings for a specific book
     * @return Illuminate\Http\Response
     */
    public function getRatingsByBook($bookId)
    {
        // Validate book exists
        $this->bookService->obtainBook($bookId);

        return $this->successResponse($this->ratingService->obtainRatingsByBook($bookId));
    }

    /**
     * Get average rating for a specific book
     * @return Illuminate\Http\Response
     */
    public function getAverageRating($bookId)
    {
        // Validate book exists
        $this->bookService->obtainBook($bookId);

        return $this->successResponse($this->ratingService->obtainAverageRating($bookId));
    }

}