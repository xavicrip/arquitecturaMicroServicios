<?php

namespace App\Http\Controllers;

use App\Rating;
use App\Services\BookService;
use App\Services\UserService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class RatingController extends Controller
{
    use ApiResponser;

    /**
     * The book service instance
     * @var BookService
     */
    public $bookService;

    /**
     * The user service instance
     * @var UserService
     */
    public $userService;

    public function __construct(BookService $bookService, UserService $userService)
    {
        $this->bookService = $bookService;
        $this->userService = $userService;
    }

    /**
     * Display a listing of ratings
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $ratings = Rating::all();
        return $this->successResponse($ratings);
    }

    /**
     * Store a newly created rating
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $rules = [
            'rating' => 'required|integer|min:1|max:5',
            'book_id' => 'required|integer',
            'user_id' => 'required|integer'
        ];

        $this->validate($request, $rules);

        // Validate book exists
        try {
            $this->bookService->obtainBook($request->book_id);
        } catch (\Exception $e) {
            return $this->errorResponse('Book not found', Response::HTTP_NOT_FOUND);
        }

        // Validate user exists
        try {
            $this->userService->obtainUser($request->user_id);
        } catch (\Exception $e) {
            return $this->errorResponse('User not found', Response::HTTP_NOT_FOUND);
        }

        // Check for duplicate rating (unique book_id + user_id)
        $existingRating = Rating::where('book_id', $request->book_id)
                                ->where('user_id', $request->user_id)
                                ->first();

        if ($existingRating) {
            return $this->errorResponse('Rating already exists for this book and user', Response::HTTP_CONFLICT);
        }

        $rating = Rating::create($request->all());
        return $this->successResponse($rating, Response::HTTP_CREATED);
    }

    /**
     * Display the specified rating
     * @param  int $ratingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($ratingId)
    {
        $rating = Rating::findOrFail($ratingId);
        return $this->successResponse($rating);
    }

    /**
     * Update the specified rating
     * @param  \Illuminate\Http\Request $request
     * @param  int $ratingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $ratingId)
    {
        $rules = [
            'rating' => 'required|integer|min:1|max:5',
            'book_id' => 'required|integer',
            'user_id' => 'required|integer'
        ];

        $this->validate($request, $rules);

        $rating = Rating::findOrFail($ratingId);

        // Validate book exists
        try {
            $this->bookService->obtainBook($request->book_id);
        } catch (\Exception $e) {
            return $this->errorResponse('Book not found', Response::HTTP_NOT_FOUND);
        }

        // Validate user exists
        try {
            $this->userService->obtainUser($request->user_id);
        } catch (\Exception $e) {
            return $this->errorResponse('User not found', Response::HTTP_NOT_FOUND);
        }

        // Check for duplicate rating (unique book_id + user_id) excluding current rating
        $existingRating = Rating::where('book_id', $request->book_id)
                                ->where('user_id', $request->user_id)
                                ->where('id', '!=', $ratingId)
                                ->first();

        if ($existingRating) {
            return $this->errorResponse('Rating already exists for this book and user', Response::HTTP_CONFLICT);
        }

        $rating->fill($request->all());
        $rating->save();

        return $this->successResponse($rating);
    }

    /**
     * Remove the specified rating
     * @param  int $ratingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($ratingId)
    {
        $rating = Rating::findOrFail($ratingId);
        $rating->delete();

        return $this->successResponse(['message' => 'Rating deleted successfully']);
    }

    /**
     * Get all ratings for a specific book
     * @param  int $bookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRatingsByBook($bookId)
    {
        // Validate book exists
        try {
            $this->bookService->obtainBook($bookId);
        } catch (\Exception $e) {
            return $this->errorResponse('Book not found', Response::HTTP_NOT_FOUND);
        }

        $ratings = Rating::where('book_id', $bookId)->get();
        return $this->successResponse($ratings);
    }

    /**
     * Get average rating for a specific book
     * @param  int $bookId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAverageRating($bookId)
    {
        // Validate book exists
        try {
            $this->bookService->obtainBook($bookId);
        } catch (\Exception $e) {
            return $this->errorResponse('Book not found', Response::HTTP_NOT_FOUND);
        }

        $averageRating = Rating::where('book_id', $bookId)
                              ->selectRaw('AVG(rating) as average_rating, COUNT(*) as total_ratings')
                              ->first();

        $result = [
            'book_id' => (int)$bookId,
            'average_rating' => $averageRating->average_rating ? round($averageRating->average_rating, 2) : null,
            'total_ratings' => $averageRating->total_ratings
        ];

        return $this->successResponse($result);
    }
}