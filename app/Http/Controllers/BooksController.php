<?php
namespace App\Http\Controllers;

// use PhpParser\Node\Stmt\TryCatch;
// use Illuminate\Validation\Rule;
use App\Models\Books;
use Illuminate\Http\Request;
use \Exception;
use Validator;
use App\Helpers\lpHttpResponses;
use App\Helpers\lpExceptionMsgHandler;

// @TODO Sampler: maybe improve this try/catch block???
// @TODO Sampler: improve error handling when adding
class BooksController extends Controller
{
    private $_perPage = 50;

    public function __construct() {
        $this->middleware('auth')->except(['getAll', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll() {
        try {
            // TODO Sampler: implement pagination
            $books  = Books::where('active', true)
                            ->orderBy('id');
                            
            $bookExists = $books->exists();
            $response   = lpApiResponse(
                false,
                ($bookExists) ? 'Books data returned successfully!': 'No books returned!',
                [
                    "books" => ($bookExists) ? $books->get(): [],
                ]
            );
            return response()->json($response, lpHttpResponses::SUCCESS);
        } catch (Exception $e) {
            $response = lpApiResponse(
                true,
                "Error while retrieving books! Message: " . lpExceptionMsgHandler::getMessage($e)
            );
            return response()->json($response, lpHttpResponses::SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $bookId
     * @return \Illuminate\Http\Response
     */
    public function show(int $bookId) {
        try {
            $book = Books::where('id', $bookId);

            $bookExists = $book->exists();
            $response   = lpApiResponse(
                false,
                ($bookExists) ? 'Book data returned successfully!': "Book #{$bookId} not found!",
                [
                    "book" => ($bookExists) ? $book->get(): [],
                ]
            );
            return response()->json($response, lpHttpResponses::SUCCESS);
        } catch (Exception $e) {
            $response = lpApiResponse(
                true,
                "Error while retrieving book #{$bookId}! Message: " . lpExceptionMsgHandler::getMessage($e)
            );
            return response()->json($response, lpHttpResponses::SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        try{
            $validator = Validator::make($request->all(), [
                'title'        => ['required', 'string', 'max:255', 'filled'],
                'isbn'         => ['required', 'string', 'size:10'],
                'published_at' => ['required', 'date', 'date_format:Y-m-d', 'before:today'],
            ]);

            // @TODO Sampler: maybe do this along with Validator::make???
            $validator->after(function ($validator) {
                $isbnString = $validator->validated()['isbn'] ?? '';

                if ($this->isValidIsbn($isbnString) !== true) {
                    $validator->errors()->add('isbn', 'Invalid ISBN number!');
                }
            });
            // =================================================
            
            if ($validator->fails()) {
                $response = lpApiResponse(
                    true,
                    'Error adding the Book!',
                    [
                        $validator->messages()
                    ]
                );
    
                return response()->json($response, lpHttpResponses::VALIDATION_FAILED);
            }
            
            $bookFields = $request->only(['title', 'isbn', 'published_at']);
            $newBook    = Books::create($bookFields);
            
            $response = lpApiResponse(
                false,
                "Book added successfully!",
                [
                    "book" => $newBook::where('id', $newBook->id)->get()
                ]
            );
            return response()->json($response, lpHttpResponses::SUCCESS);
        } catch (Exception $e) {
            $response = lpApiResponse(
                true,
                "Error adding the book! Message: " . lpExceptionMsgHandler::getMessage($e)
            );
            return response()->json($response, lpHttpResponses::SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $bookId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $bookId) {
        try{
            $validator = Validator::make($request->all(), [
                'title'        => ['string', 'max:255', 'filled'],
                'isbn'         => ['size:10', 'filled'],
                'published_at' => ['date', 'date_format:Y-m-d', 'before:today'],
            ]);

            // @TODO check if this is the best way to validate id
            if(!is_int($bookId) && $bookId <= 0){
                $validator->errors()->add('id', 'Inform a valid ID!');
            }
            // ==================================================

            // @TODO Sampler: maybe do this along with Validator::make???
            $validator->after(function ($validator) {
                $isbnString = $validator->validated()['isbn'] ?? '';

                if ($this->isValidIsbn($isbnString, true) !== true) {
                    $validator->errors()->add('isbn', 'Invalid ISBN number!');
                }
            });
            // =================================================
            
            if ($validator->fails()) {
                $response = lpApiResponse(
                    true,
                    'Error updating the Book!',
                    [
                        $validator->messages()
                    ]
                );
    
                return response()->json($response, lpHttpResponses::VALIDATION_FAILED);
            }
            
            $book = Books::where('id', $bookId);
            if(!$book->exists()){
                $response = lpApiResponse(
                    false,
                    "Book #{$bookId} not found!"
                );
                return response()->json($response, lpHttpResponses::SUCCESS);
            }
            
            $bookFields = $request->only(['title', 'isbn', 'published_at']);
            Books::where('id', $bookId)
                    ->update($bookFields);
            
            $response = lpApiResponse(
                false,
                "Book updated successfully!",
                [
                    "book" => Books::where('id', $bookId)->get()
                ]
            );
            return response()->json($response, lpHttpResponses::SUCCESS);
        } catch (Exception $e) {
            $response = lpApiResponse(
                true,
                "Error updating the book! Message: " . lpExceptionMsgHandler::getMessage($e)
            );
            return response()->json($response, lpHttpResponses::SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Books  $books
     * @return \Illuminate\Http\Response
     */
    public function destroy(Books $books)
    {
        //
    }

    /**
     * Function to check if isbn number is valid. Can bypass the check when updating the record.
     *
     * @param string $isbn
     * @param boolean $allowEmpty [default false]
     * @return boolean
     */
    private function isValidIsbn(string $isbn, bool $allowEmpty = false) {
        if($allowEmpty && strlen(trim($isbn)) <= 0) {
            return true;
        }
        else {
            return lpValidateIsbn($isbn);
        }
    }
}
