<?php

namespace App\Http\Controllers;

use App\Review;
use App\Services\BookService;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use GuzzleHttp\Exception\ClientException;

class ReviewController extends Controller
{
    use ApiResponser;

    /**
     * @var BookService
     */
    public $bookService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BookService $bookService)
    {
        $this->bookService = $bookService;
    }

    /**
     * Return the list of reviews
     * @return Illuminate\Http\Response
     */
    public function index()
    {
        $reviews = Review::all();
        return $this->successResponse($reviews);
    }

    /**
     * Create one new review
     * @return Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'comment' => 'required|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'book_id' => 'required|integer|min:1',
        ];

        $this->validate($request, $rules);

        // Check if book exists
        try {
            $this->bookService->obtainBook($request->book_id);
        } catch (ClientException $e) {
            return $this->errorResponse('Book not found', Response::HTTP_NOT_FOUND);
        }

        $review = Review::create($request->all());
        return $this->successResponse($review, Response::HTTP_CREATED);
    }

    /**
     * Obtains and show one review
     * @return Illuminate\Http\Response
     */
    public function show($review)
    {
        $review = Review::findOrFail($review);
        return $this->successResponse($review);
    }

    /**
     * Update an existing review
     * @return Illuminate\Http\Response
     */
    public function update(Request $request, $review)
    {
        $rules = [
            'comment' => 'max:255',
            'rating' => 'integer|min:1|max:5',
            'book_id' => 'integer|min:1',
        ];

        $this->validate($request, $rules);

        $review = Review::findOrFail($review);

        // Check if book exists if book_id is being updated
        if ($request->has('book_id')) {
            try {
                $this->bookService->obtainBook($request->book_id);
            } catch (ClientException $e) {
                return $this->errorResponse('Book not found', Response::HTTP_NOT_FOUND);
            }
        }

        $review->fill($request->all());

        if ($review->isClean()) {
            return $this->errorResponse('At least one value must change', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $review->save();
        return $this->successResponse($review);
    }

    /**
     * Remove an existing review
     * @return Illuminate\Http\Response
     */
    public function destroy($review)
    {
        $review = Review::findOrFail($review);
        $review->delete();
        return $this->successResponse($review);
    }
}