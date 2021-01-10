<?php
namespace App\Http\Controllers;

// use PhpParser\Node\Stmt\TryCatch;
// use Illuminate\Validation\Rule;
use App\Models\Books;
use Illuminate\Http\Request;
use Validator;
use \Exception;
use App\Helpers\lpHttpResponses;
use App\Helpers\lpExceptionMsgHandler;

// @TODO Sampler: maybe improve this try/catch block???
// @TODO Sampler: improve error handling when adding
class BooksController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth')->except(['getAll', 'show']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        try
        {
            $Books    = new Books();
            $response = $Books->getBooks(
                [
                    'active' => true
                ]
            );

            return response()->json($response, lpHttpResponses::SUCCESS);
        }
        catch (Exception $e)
        {
            return lpExceptionMsgHandler::controllerExceptionHandler($e, 'Error while retrieving books!');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $bookId
     * @return \Illuminate\Http\Response
     */
    public function show(int $bookId)
    {
        try
        {
            $Books    = new Books();
            $response = $Books->getBooks(
                [
                    'id' => $bookId
                ]
            );

            return response()->json($response, lpHttpResponses::SUCCESS);
        }
        catch (Exception $e)
        {
            return lpExceptionMsgHandler::controllerExceptionHandler($e, "Error while retrieving book #{$bookId}!");
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try
        {
            $bookFields = $request->only(['title', 'isbn', 'published_at']);
            $Books      = new Books();
            $retSave    = $Books->addBook($bookFields);

            return response()->json($retSave, lpHttpResponses::SUCCESS);
        }
        catch (Exception $e)
        {
            return lpExceptionMsgHandler::controllerExceptionHandler($e, 'Error adding the book!');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $bookId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $bookId)
    {
        try
        {
            $bookFields = $request->only(['title', 'isbn', 'published_at']);
            $Books      = new Books();
            $retSave    = $Books->updateBook($bookId, $bookFields);

            return response()->json($retSave, lpHttpResponses::SUCCESS);
        }
        catch (Exception $e)
        {
            return lpExceptionMsgHandler::controllerExceptionHandler($e, 'Error editing the book!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  integer  $bookId
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $bookId)
    {
        try
        {
            $Books     = new Books();
            $retDelete = $Books->deleteBook($bookId);
            return response()->json($retDelete, lpHttpResponses::SUCCESS);
        }
        catch (Exception $e)
        {
            return lpExceptionMsgHandler::controllerExceptionHandler($e, "Error deleting the book #{$bookId}!");
        }
    }

    /**
     * Activate/Deactivate the specified resource.
     *
     * @param  integer  $bookId
     * @param  integer  $activate [0 deactivate | 1 activate]
     * @return \Illuminate\Http\Response
     */
    public function activate(int $bookId, int $activate)
    {
        $bActive = (bool) $activate;
        $sActive = ($bActive) ? 'activated': 'deactivated';

        try {
            $Books   = new Books();
            $retSave = $Books->updateBook($bookId, [
                'active' => $bActive
            ]);
            
            // just add the 'word' activated/deactivated when success
            if (!$retSave['error'])
            {
                $retSave['message'] .= " Action: {$sActive}";
            }

            return response()->json($retSave, lpHttpResponses::SUCCESS);
        }
        catch (Exception $e)
        {
            return lpExceptionMsgHandler::controllerExceptionHandler($e, "Error {$sActive} the book #{$bookId}!");
        }
    }

    public function checkIn(int $bookId)
    {
        try
        {
            $Books      = new Books();
            $retCheckIn = $Books->checkinBook($bookId);
            return response()->json($retCheckIn, lpHttpResponses::SUCCESS);
        }
        catch (Exception $e)
        {
            return lpExceptionMsgHandler::controllerExceptionHandler($e, "Check-in process error for book #{$bookId}!");
        }
    }

}
