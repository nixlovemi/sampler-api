<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Users;
use Validator;

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

        return response()->json($response, Response::HTTP_OK);
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
            'email'    => Users::NEW_USER_RULES['email'] ?? ['required', 'string', 'min:2'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        if ($validator->fails())
        {
            $response = lpApiResponse(true, 'Error logging in!', [$validator->messages()]);
            return response()->json($response, Response::HTTP_OK);
        }
        else
        {
            $credentials = $request->only(['email', 'password']);
            if (!$token = auth()->attempt($credentials))
            {
                $response = lpApiResponse(true, 'Invalid Credentials.');
                return response()->json($response, Response::HTTP_UNAUTHORIZED);
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
        $response = lpApiResponse(false, 'Successfully logged out!');
        return response()->json($response, Response::HTTP_OK);
    }

    /**
     * Returns the message when user is unauthenticated
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unauthenticated()
    {
        $response = lpApiResponse(
            true,
            'Please authenticate before using this route'
        );

        return response()->json($response, Response::HTTP_UNAUTHORIZED);
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

        return response()->json($response, Response::HTTP_OK);
    }
}