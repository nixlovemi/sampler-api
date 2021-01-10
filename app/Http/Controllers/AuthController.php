<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Helpers\lpHttpResponses;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['login', 'unauthenticated']);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $retUser = auth()->user();

        $response = lpApiResponse(
            false,
            'Logged user data returned successfully!',
            [
                "user" => $retUser->getAttributes()
            ]
        );

        return response()->json($response, lpHttpResponses::SUCCESS);
    }

    /**
     * Login the user and return the JWT token
     *
     * @param Request $request [email, password]
     * @return json []
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails())
        {
            $response = lpApiResponse(
                true,
                'Error logging in!',
                [
                    $validator->messages()
                ]
            );

            return response()->json($response, lpHttpResponses::SUCCESS);
        }
        else
        {
            $credentials = $request->only(['email', 'password']);
            if (!$token = auth()->attempt($credentials))
            {
                $response = lpApiResponse(
                    true,
                    'Invalid Credentials.'
                );

                return response()->json($response, lpHttpResponses::UNAUTHORIZED);
            }
            
            return $this->respondWithToken($token);
        }
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        $response = lpApiResponse(
            false,
            'Successfully logged out!'
        );

        return response()->json($response, lpHttpResponses::SUCCESS);
    }

    /**
     * Returns the message when user is unauthenticated
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unauthenticated()
    {
        // @TODO Sampler: try to change the response for {"message": "Unauthenticated."}
        $response = lpApiResponse(
            true,
            'Please authenticate before using this route'
        );

        return response()->json($response, lpHttpResponses::UNAUTHORIZED);
    }

    /**
     * Return the array with token information
     *
     * @param [type] $token
     * @return \Illuminate\Http\JsonResponse
     */
    private function respondWithToken($token)
    {
        $response = lpApiResponse(
            false,
            'Successfully logged in!',
            [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]
        );

        return response()->json($response, lpHttpResponses::SUCCESS);
    }
}