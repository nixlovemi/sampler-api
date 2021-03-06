<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Validator;

class UserActionLogs extends Model
{
    public $timestamps = false;
    public const NEW_USER_ACT_LOG_RULES = [
        'book_id'    => ['required', 'integer', 'gt:0', 'filled'],
        'user_id'    => ['required', 'integer', 'gt:0', 'filled'],
        'action'     => ['required', 'string', 'in:CHECKIN,CHECKOUT'],
        'created_at' => ['required', 'date', 'date_format:Y-m-d H:i:s', 'before:tomorrow'],
    ];
    public const USER_ACT_LOG_ACTION_CHECKIN  = 'CHECKIN';
    public const USER_ACT_LOG_ACTION_CHECKOUT = 'CHECKOUT';

    public function user()
    {
        return $this->belongsTo('App\Models\Users');
    }

    public function book()
    {
        return $this->belongsTo('App\Models\Books');
    }

    /**
     * Adds a User Action Log
     *
     * @param array $UserActionsLogData [key/value with the name/value of the table fields. Ex: ['book_id' => 1, 'user_id' => 2] ...]
     * @return array lpApiResponse
     */
    public function addLog(array $UserActionsLogData)
    {
        // check empty $UserActionsLogData
        if (count($UserActionsLogData) <= 0)
        {
            return lpApiResponse(true, 'Empty user action log data!');
        }

        // check rules for adding a user action log
        $validator = Validator::make($UserActionsLogData, UserActionLogs::NEW_USER_ACT_LOG_RULES);
        if ($validator->fails())
        {
            return lpApiResponse(true, 'Error adding user action log data!', [
                "validations" => $validator->messages()
            ]);
        }

        // fill model
        $UALog             = new UserActionLogs;
        $UALog->book_id    = $UserActionsLogData['book_id'] ?? NULL;
        $UALog->user_id    = $UserActionsLogData['user_id'] ?? NULL;
        $UALog->action     = $UserActionsLogData['action'] ?? NULL;
        $UALog->created_at = $UserActionsLogData['created_at'] ?? NULL;

        // all good, save
        $UALog->save();
        $UALog->refresh();

        // get new added user action log
        return lpApiResponse(false, 'User action log added successfully!', [
            "book" => $UALog
        ]);
    }

    /**
     * Return the last log for this book id or null if it doesn't exists
     *
     * @param integer $bookId
     * @return Books|null
     */
    public function getBookLastLog(int $bookId)
    {
        // get last inserted log
        $BookLastLog = UserActionLogs::where('book_id', $bookId)
                                        ->orderByDesc('id')
                                        ->limit(1);
        
        return (!$BookLastLog->exists()) ? null: $BookLastLog->first();
    }
}
