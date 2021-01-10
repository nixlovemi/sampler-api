<?php
namespace App\Models;
use Validator;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;

// class Users extends Model
class Users extends Authenticatable implements JWTSubject
{
    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'active' => true
    ];

    public $timestamps = false; // prevent created/updated_at
    public const NEW_USER_RULES = [
        'email'         => ['required', 'email:rfc,dns', 'max:255', 'filled'],
        'name'          => ['required', 'string', 'min:2', 'max:255', 'filled', "regex:/^[a-zA-Z]+(([',. -][a-zA-Z ])?[a-zA-Z]*)*$/"], // regex for names (can a name have numbers on it?)
        'password'      => ['required', 'string', 'min:8', 'max:255', 'regex:/^(?=.*\d)(?=.*[A-Z])(?!.*[^a-zA-Z0-9@#$^+=])(.{8,255})$/'],
        'date_of_birth' => ['required', 'date', 'date_format:Y-m-d', 'before:today'],
    ];
    public const NEW_USER_CST_MSG = [
        'name.regex'     => 'Enter a valid name.',
        'password.regex' => 'The password must have 1 capital letter and 1 number.'
    ];

    public function logs()
    {
        return $this->hasMany('App\Models\UserActionLogs');
    }

    /**
     * Returns the logged user id
     *
     * @return integer|null
     */
    public static function getLoggedUserId()
    {
        return auth()->user()->getAttributes()['id'] ?? null;
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
        $User->email         = $UserData['email'] ?? NULL;
        $User->name          = $UserData['name'] ?? NULL;
        $User->password      = $UserData['password'] ?? NULL;
        $User->date_of_birth = $UserData['date_of_birth'] ?? NULL;

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
        $User->refresh();

        // get new added user and returns
        return lpApiResponse(false, 'User added successfully!', [
            "user" => $User
        ]);
    }
}
