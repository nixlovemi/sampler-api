<?php
namespace App\Http\Controllers;

use App\Models\Users;
use Illuminate\Http\Request;
use \Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\lpExceptionMsgHandler;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['store']);
        $this->middleware('superuser')->except(['store', 'update', 'activate']);
    }

    /**
     * Display a listing of the resource. Only superusers can get the user's list.
     *
     * @return \Illuminate\Http\Response
     */
    public function getAll()
    {
        try
        {
            $Users    = new Users();
            $response = $Users->getUsers(
                [
                    'active' => true
                ]
            );

            return response()->json($response, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, 'Error while retrieving users!');
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource. Only superuser can get user register by ID.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function show(int $userId)
    {
        try
        {
            $Users    = new Users();
            $response = $Users->getUsers(
                [
                    'id' => $userId
                ]
            );

            return response()->json($response, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, "Error while retrieving user #{$userId}!");
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
            $userFields = $request->only(['email', 'name', 'password', 'date_of_birth']);
            $Users      = new Users();
            $retSave    = $Users->addUser($userFields);

            return response()->json($retSave, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, 'Error adding the user!');
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int $userId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $userId)
    {
        try
        {
            // users can change only their own ID; superuser can bypass this
            if (!Users::isSuperuser(Users::getLoggedUserId()) && $userId != Users::getLoggedUserId())
            {
                $message = lpApiResponse(true, "Can't change other user register.");
                return response()->json($message, Response::HTTP_FORBIDDEN);
            }

            $userFields = $request->only(['email', 'name', 'password', 'date_of_birth']);
            $Users      = new Users();
            $retSave    = $Users->updateUser($userId, $userFields);

            return response()->json($retSave, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, 'Error editing the user!');
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage. Only superuser can delete users.
     *
     * @param  integer  $userId
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $userId)
    {
        try
        {
            $Users     = new Users();
            $retDelete = $Users->deleteUser($userId);
            return response()->json($retDelete, Response::HTTP_OK);
        }
        catch (Exception $e)
        {
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, "Error deleting the user #{$userId}!");
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Activate/Deactivate the specified resource.
     *
     * @param  integer  $userId
     * @param  integer  $activate [0 deactivate | 1 activate]
     * @return \Illuminate\Http\Response
     */
    public function activate(int $userId, int $activate)
    {
        $bActive = (bool) $activate;
        $sActive = ($bActive) ? 'activated': 'deactivated';

        try {
            $Users   = new Users();
            $retSave = $Users->updateUser($userId, [
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
            $return = lpExceptionMsgHandler::controllerExceptionHandler($e, "Error {$sActive} the user #{$userId}!");
            return response()->json($return, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
