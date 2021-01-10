<?php
namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;
use Validator;
use \Exception;
use App\Helpers\lpHttpResponses;
use App\Helpers\lpExceptionMsgHandler;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['store']);
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
            $userFields = $request->only(['email', 'name', 'password', 'date_of_birth']);
            $Users      = new Users();
            $retSave    = $Users->addUser($userFields);

            return response()->json($retSave, lpHttpResponses::SUCCESS);
        }
        catch (Exception $e)
        {
            return lpExceptionMsgHandler::controllerExceptionHandler($e, 'Error adding the user!');
        }
    }
}
