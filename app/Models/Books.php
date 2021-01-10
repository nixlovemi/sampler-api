<?php
namespace App\Models;
use Validator;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Books extends Authenticatable
{
    public $timestamps = false;
    protected $fillable = ['title', 'isbn', 'published_at'];
    public const NEW_BOOK_RULES = [
        'title'        => ['required', 'string', 'max:255', 'filled'],
        'isbn'         => ['required', 'string', 'size:10', 'filled'],
        'published_at' => ['required', 'date', 'date_format:Y-m-d', 'before:today', 'filled'],
    ];

    public function logs()
    {
        return $this->hasMany('App\Models\UserActionLogs');
    }

    /**
     * Get all the books with optional filters
     *
     * @param array $filters [active: bool]
     * @return array lpApiResponse
     */
    public function getBooks($filters=[])
    {
        // filters
        $id     = $filters['id'] ?? null;
        $active = $filters['active'] ?? null;

        // filter the database
        // TODO Sampler: implement pagination
        $books = Books::select('*');
        if ($id !== null)
        {
            $books->where('id', $id);
        }
        if ($active !== null)
        {
            $books->where('active', $active);
        }
        $books->orderBy('id');

        // messages
        $booksExists = $books->exists();
        $message     = ($booksExists) ? 'Book data returned successfully!': 'No books returned!';
        $arrBooks    = ($booksExists) ? $books->get(): [];

        return lpApiResponse(false, $message, [
            'books' => $arrBooks
        ]);
    }

    /**
     * Adds a new book
     *
     * @param array $BookData [key/value with the name/value of the table fields. Ex: ['tilte' => 'harry potter', 'isbn' => '1234567980'] ...]
     * @return array lpApiResponse
     */
    public function addBook(array $BookData)
    {
        // check empty $BookData
        if (count($BookData) <= 0)
        {
            return lpApiResponse(true, 'Empty book data!');
        }

        // check rules for adding a new book
        $validator = Validator::make($BookData, Books::NEW_BOOK_RULES);
        if ($validator->fails())
        {
            return lpApiResponse(true, 'Error adding the Book!', [
                "validations" => $validator->messages()
            ]);
        }

        // fill model
        $Book               = new Books;
        $Book->title        = $BookData['title'] ?? '';
        $Book->isbn         = $BookData['isbn'] ?? '';
        $Book->published_at = $BookData['published_at'] ?? '';

        // validate the isbn number
        if ($this->isValidIsbn($Book->isbn) !== true)
        {
            return lpApiResponse(true, 'Error adding the Book!', [
                "validations" => [
                    'isbn' => 'Invalid ISBN number!'
                ]
            ]);
        }

        // check if isbn already exists | UK
        $retChkEmail = Books::where('isbn', $Book->isbn);
        if ($retChkEmail->exists())
        {
            return lpApiResponse(true, 'ISBN already exists!');
        }

        // all good, save
        $Book->save();

        // get new added book and returns
        return lpApiResponse(false, 'Book added successfully!', [
            "book" => Books::where('id', $Book->id)->get()
        ]);
    }

    /**
     * Updates a new book
     *
     * @param integer $bookId
     * @param array $BookData [key/value with the name/value of the table fields. Ex: ['tilte' => 'harry potter', 'isbn' => '1234567980'] ...]
     * @return array lpApiResponse
     */
    public function updateBook(int $bookId, array $BookData)
    {
        // check empty $BookData
        if (count($BookData) <= 0)
        {
            return lpApiResponse(true, 'Empty book data!');
        }

        // get rules and remove the required param
        $arrUpdateRules = [];
        foreach (Books::NEW_BOOK_RULES as $ruleKey => $arrRules)
        {
            $arrUpdateRules[$ruleKey] = array_filter($arrRules, function($value) {
                return $value != 'required';
            });
        }

        // check rules for editing a book
        $validator = Validator::make($BookData, $arrUpdateRules);
        if ($validator->fails())
        {
            return lpApiResponse(true, 'Error editing the Book!', [
                "validations" => $validator->messages()
            ]);
        }

        // validate the isbn number
        if (isset($BookData['isbn']) && $this->isValidIsbn($BookData['isbn'], true) !== true)
        {
            return lpApiResponse(true, 'Error editing the Book!', [
                "validations" => [
                    'isbn' => 'Invalid ISBN number!'
                ]
            ]);
        }

        // get the book by id
        $retBook = Books::where('id', $bookId);
        if (!$retBook->exists())
        {
            return lpApiResponse(true, "Book #{$bookId} not found!");
        }

        // retrive book from DB
        $Book = $retBook->first();

        // if isbn changed, check if the new isbn already exists | UK
        if (isset($BookData['isbn']) && $Book->isbn != $BookData['isbn'])
        {
            $retChkEmail = Books::where('isbn', $BookData['isbn']);
            if ($retChkEmail->exists())
            {
                return lpApiResponse(true, 'ISBN already exists!');
            }
        }

        // all good, update
        Books::where('id', $bookId)
                ->update($BookData);

        // get new edited book and returns
        return lpApiResponse(false, 'Book edited successfully!', [
            "book" => Books::where('id', $bookId)->get()
        ]);
    }

    /**
     * Deletes a book
     *
     * @param integer $bookId
     * @return array lpApiResponse
     */
    public function deleteBook(int $bookId)
    {
        // get the book by id
        $retBook = Books::where('id', $bookId);
        if (!$retBook->exists())
        {
            return lpApiResponse(true, "Book #{$bookId} not found!");
        }

        // all good, delete
        $isDeleted = ($retBook->delete() == 1);
        $strDelete = ($isDeleted) ? 'Book successfully deleted!': "Error deleting the book #{$bookId}!";

        return lpApiResponse(!$isDeleted, $strDelete);
    }

    /**
     * Function to check if isbn number is valid. Can bypass the check when updating the record.
     *
     * @param string $isbn
     * @param boolean $allowEmpty [default false]
     * @return boolean
     */
    private function isValidIsbn(string $isbn, bool $allowEmpty = false)
    {
        if ($allowEmpty && strlen(trim($isbn)) <= 0)
        {
            return true;
        }
        else
        {
            return lpValidateIsbn($isbn);
        }
    }
}
