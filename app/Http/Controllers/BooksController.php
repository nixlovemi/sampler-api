<?php
namespace App\Http\Controllers;

use App\Models\Books;
use Illuminate\Http\Request;
use \Exception;
use App\Helpers\lpExceptionMsgHandler;
use Symfony\Component\HttpFoundation\Response;

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

            return response()->json($response, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, 'Error while retrieving books!');
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
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

            return response()->json($response, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, "Error while retrieving book #{$bookId}!");
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
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

            return response()->json($retSave, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, 'Error adding the book!');
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
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

            return response()->json($retSave, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, 'Error editing the book!');
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
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
            return response()->json($retDelete, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, "Error deleting the book #{$bookId}!");
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
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

            return response()->json($retSave, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, "Error {$sActive} the book #{$bookId}!");
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check-out a book
     *
     * @param integer $bookId
     * @return \Illuminate\Http\Response
     */
    public function checkOut(int $bookId)
    {
        try
        {
            $Books       = new Books();
            $retCheckOut = $Books->checkoutBook($bookId);
            return response()->json($retCheckOut, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, "Check-out process error for book #{$bookId}!");
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check-in a book
     *
     * @param integer $bookId
     * @return \Illuminate\Http\Response
     */
    public function checkIn(int $bookId)
    {
        try
        {
            $Books      = new Books();
            $retCheckIn = $Books->checkinBook($bookId);
            return response()->json($retCheckIn, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, "Check-in process error for book #{$bookId}!");
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
