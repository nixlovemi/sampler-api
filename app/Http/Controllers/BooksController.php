<?php
namespace App\Http\Controllers;

//use PhpParser\Node\Stmt\TryCatch;
use App\Models\Books;
use Illuminate\Http\Request;
use \Exception;
use Validator;
use Illuminate\Validation\Rule;
use App\Helpers\lpHttpResponses;

// @TODO maybe improve this try/catch block???
// @TODO improve error handling when adding
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
    public function getAll(/*$page=1*/) {
        try {
            /*$offset = $page - 1; // zero based query*/
            $books  = Books::where('active', true)
                            ->orderBy('id')
                            /*->offset($offset)*/
                            ->limit($this->_perPage);

            if($books->exists()){
                $response = lpApiResponse(
                    false,
                    'Books data returned successfully!',
                    [
                        "books" => $books->get(),
                        /*"page"  => $page*/
                    ]
                );
            } else {
                $response = lpApiResponse(
                    false,
                    'No books returned!',
                    [
                        "books" => []
                    ]
                );
            }
            return response()->json($response, lpHttpResponses::SUCCESS);
        } catch (Exception $e) {
            $response = lpApiResponse(
                true,
                "Error while retrieving books! Message: {$e->getMessage()}"
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
    public function show($bookId) {
        try {
            $book = Books::where('id', $bookId);

            if($book->exists()){
                $response = lpApiResponse(
                    false,
                    'Book data returned successfully!',
                    [
                        "book" => $book->get(),
                    ]
                );
            } else {
                $response = lpApiResponse(
                    false,
                    "Book #{$bookId} not found!",
                    [
                        "book" => []
                    ]
                );
            }
            return response()->json($response, lpHttpResponses::SUCCESS);
        } catch (Exception $e) {
            $response = lpApiResponse(
                true,
                "Error while retrieving book #{$bookId}! Message: {$e->getMessage()}"
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
                'title'        => 'required|string|max:255',
                'isbn'         => 'required|size:10', // ISBN validator
                'published_at' => 'required|date|date_format:Y-m-d|before:today',
                // 'status'       => ['required', 'string', Rule::in(['CHECKED_OUT', 'AVAILABLE'])]
            ]);
            
            if ($validator->fails()) {
                $response = lpApiResponse(
                    true,
                    'Error adding a Book!',
                    [
                        $validator->messages()
                    ]
                );
    
                return response()->json($response, lpHttpResponses::VALIDATION_FAILED);
            }
            else {
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
            }
        } catch (Exception $e) {
            $response = lpApiResponse(
                true,
                "Error adding the book! Message: {$e->getMessage()}"
            );
            return response()->json($response, lpHttpResponses::SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Books  $books
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Books $books)
    {
        //
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
}
