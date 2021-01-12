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
        'active'    => true,
        'superuser' => false,
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
    private const HIDDEN_FIELDS = ['password', 'superuser'];

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

    public static function isSuperuser(int $userId)
    {
        $retSU = Users::where('id', $userId)->where('superuser', true);
        return $retSU->exists();
    }

    /**
     * Get all the users with optional filters
     *
     * @param array $filters [active: bool]
     * @return array lpApiResponse
     */
    public function getUsers($filters=[])
    {
        // filters
        $id     = $filters['id'] ?? null;
        $active = $filters['active'] ?? null;

        // filter the database
        // TODO Sampler: implement pagination
        $users = Users::select('id', 'email', 'name', 'date_of_birth', 'active');
        if ($id !== null)
        {
            $users->where('id', $id);
        }
        if ($active !== null)
        {
            $users->where('active', $active);
        }
        $users->orderBy('id');

        // messages
        $usersExists = $users->exists();
        $message     = ($usersExists) ? 'User data returned successfully!': 'No users returned!';
        $arrUsers    = ($usersExists) ? $users->get(): [];

        return lpApiResponse(false, $message, [
            'users' => $arrUsers
        ]);
    }

    /**
     * Adds a new user
     *
     * @param array $UserData [key/value with the name/value of the table fields. Ex: ['name' => 'leandro', 'email' => 'leandro@sampler.io'] ...]
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
            return lpApiResponse(true, 'Error adding the User!', [
                'validations' => [
                    'email' => 'Email already exists!'
                ]
            ]);
        }
        
        // all good, save
        $User->save();
        $User->refresh();
        $User->setHidden(Users::HIDDEN_FIELDS);

        // get new added user and returns
        return lpApiResponse(false, 'User added successfully!', [
            "user" => $User
        ]);
    }

    /**
     * Updates a user
     *
     * @param integer $userId
     * @param array $UserData [key/value with the name/value of the table fields. Ex: ['name' => 'leandro', 'email' => 'leandro@sampler.io'] ...]
     * @return array lpApiResponse
     */
    public function updateUser(int $userId, array $UserData)
    {
        // users can change only their own ID; superuser can bypass this
        if (!Users::isSuperuser(Users::getLoggedUserId()) && $userId != Users::getLoggedUserId())
        {
            return lpApiResponse(true, "Can't change other user register.");
        }

        // check empty $UserData
        if (count($UserData) <= 0)
        {
            return lpApiResponse(true, 'Empty user data!');
        }

        // get rules and remove the required param
        $arrUpdateRules = [];
        foreach (Users::NEW_USER_RULES as $ruleKey => $arrRules)
        {
            $arrUpdateRules[$ruleKey] = array_filter($arrRules, function($value) {
                return $value != 'required';
            });
        }

        // check rules for editing a user
        $validator = Validator::make($UserData, $arrUpdateRules);
        if ($validator->fails())
        {
            return lpApiResponse(true, 'Error editing the User!', [
                "validations" => $validator->messages()
            ]);
        }

        // get the user by id
        $User = Users::find($userId);
        if (empty($User))
        {
            return lpApiResponse(true, "User #{$userId} not found!");
        }

        // if email changed, check if the new email already exists | UK
        if (isset($UserData['email']) && $User->email != $UserData['email'])
        {
            $retChkEmail = Users::where('email', $UserData['email']);
            if ($retChkEmail->exists())
            {
                return lpApiResponse(true, 'Error editing the User!', [
                    'validations' => [
                        'email' => 'Email already exists!'
                    ]
                ]);
            }
        }

        // bcrypt the password
        if (isset($UserData['password']))
        {
            $UserData['password'] = bcrypt($UserData['password']);
        }

        // all good, update
        Users::where('id', $userId)
                ->update($UserData);

        // get new edited user and returns
        return lpApiResponse(false, 'User edited successfully!', [
            "user" => Users::findOrFail($userId)->setHidden(Users::HIDDEN_FIELDS)
        ]);
    }

    /**
     * Deletes a user
     *
     * @param integer $userId
     * @return array lpApiResponse
     */
    public function deleteUser(int $userId)
    {
        // get the user by id
        $User = Users::find($userId);
        if (empty($User))
        {
            return lpApiResponse(true, "User #{$userId} not found!");
        }

        // all good, delete
        $isDeleted = ($User->delete() == 1);
        $strDelete = ($isDeleted) ? 'User successfully deleted!': "Error deleting the user #{$userId}!";

        return lpApiResponse(!$isDeleted, $strDelete);
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
}
