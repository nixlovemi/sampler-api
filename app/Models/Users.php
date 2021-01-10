<?php
namespace App\Models;
use Validator;
use \Exception;
use App\Helpers\lpExceptionMsgHandler;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

// class Users extends Model
class Users extends Authenticatable implements JWTSubject
{
    public $timestamps = false; // prevent created/updated_at
    public const NEW_USER_RULES = [
        'email'         => ['required', 'email:rfc,dns', 'max:255', 'filled'],
        'name'          => ['required', 'string', 'min:2', 'max:255', 'filled'],
        'password'      => ['required', 'string', 'min:8', 'max:255', 'regex:/^(?=.*\d)(?=.*[A-Z])(?!.*[^a-zA-Z0-9@#$^+=])(.{8,255})$/'],
        'date_of_birth' => ['required', 'date', 'date_format:Y-m-d', 'before:today'],
    ];
    public const NEW_USER_CST_MSG = [
        'password.regex' => 'The password must have 1 capital letter and 1 number.'
    ];

    public function logs()
    {
        return $this->hasMany('App\Models\UserActionLogs');
    }

     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Adds a new user
     *
     * @param array $UserData [key/value with the name/value of the table fields. Ex: ['name' => 'leandro', 'email' => 'leandro@leandro.com'] ...]
     * @return array lpApiResponse
     */
    public function addUser(array $UserData)
    {
        // check rules for adding a new user
        $validator = Validator::make($UserData, Users::NEW_USER_RULES, Users::NEW_USER_CST_MSG);
        if ($validator->fails())
        {
            return lpApiResponse(true, 'Error adding the User!', [
                "validations" => $validator->messages()
            ]);
        }

        // fill model
        $User                = new Users;
        $User->email         = $UserData['email'] ?? '';
        $User->name          = $UserData['name'] ?? '';
        $User->password      = $UserData['password'] ?? '';
        $User->date_of_birth = $UserData['date_of_birth'] ?? '';

        // bcrypt the password
        $User->password = bcrypt($User->password);

        // check if email already exists | UK
        $retChkEmail = Users::where('email', $User->email);
        if ($retChkEmail->exists())
        {
            return lpApiResponse(true, 'Email already exists!');
        }
        
        // all good, save
        $User->save();

        // get new added user and returns
        return lpApiResponse(false, 'User added successfully!', [
            "user" => Users::where('id', $User->id)->get()
        ]);
    }
}
