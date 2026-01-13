<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Services\ReviewService;
use Illuminate\Http\Response;
use App\Services\BookService;

class ReviewController extends Controller
{
    use ApiResponser;

    /**
     * The service to consume the review service
     * @var ReviewService
     */
    public $reviewService;

    /**
     * The service to consume the book service
     * @var BookService
     */
    public $bookService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ReviewService $reviewService, BookService $bookService)
    {
        $this->reviewService = $reviewService;
        $this->bookService = $bookService;
    }

    /**
     * Retrieve and show all the Reviews
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        return $this->successResponse($this->reviewService->obtainReviews());
    }

    /**
     * Creates an instance of Review
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->bookService->obtainBook($request->book_id);

        return $this->successResponse($this->reviewService->createReview($request->all()), Response::HTTP_CREATED);
    }

    /**
     * Obtain and show an instance of Review
     * @return Illuminate\Http\Response
     */
    public function show($review)
    {
        return $this->successResponse($this->reviewService->obtainReview($review));
    }

    /**
     * Updated an instance of Review
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $review)
    {
        // Validate book_id if provided
        if ($request->has('book_id')) {
            $this->bookService->obtainBook($request->book_id);
        }

        return $this->successResponse($this->reviewService->editReview($request->all(), $review));
    }

    /**
     * Removes an instance of Review
     * @return Illuminate\Http\Response
     */
    public function destroy($review)
    {
        return $this->successResponse($this->reviewService->deleteReview($review));
    }

}