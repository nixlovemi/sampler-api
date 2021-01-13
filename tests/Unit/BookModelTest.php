<?php
namespace Tests\Unit;
use App\Models\Books;
use App\Models\Users;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BookModelTest extends TestCase
{
    use DatabaseTransactions;

    // @TODO Sampler: improve this
    private $_userEmail    = 'testing@sampler.io';
    private $_userName     = 'Test Sampler';
    private $_userPassword = 'Sampler123';
    private $_userBDate    = '1985-03-15';

    public static function createTestBook(array $Book)
    {
        $Book = Books::firstOrCreate(
            ['isbn' => $Book['isbn'] ?? null],
            [
                'title'        => $Book['title'] ?? null,
                'published_at' => $Book['published_at'] ?? null,
            ]
        );
        BookModelTest::assertTrue($Book->exists(), 'Create test book failed');
        return $Book;
    }

    public function testAddBookSucess()
    {
        $bookData = [
            'title'        => 'Harry Potter 100',
            'isbn'         => '8508136110',
            'published_at' => '1985-03-15',
        ];
        $Books      = new Books();
        $retAddBook = $Books->addBook($bookData);
        
        $this->assertIsArray($retAddBook, 'Return is not array');
        $this->assertArrayHasKey('error', $retAddBook, 'Array key "error" does not exist');
        $this->assertArrayHasKey('message', $retAddBook, 'Array key "message" does not exist');
        $this->assertFalse($retAddBook['error'], 'Add book returned false');

        $this->assertArrayHasKey('data', $retAddBook, 'Array key "data" does not exist');
        $this->assertArrayHasKey('book', $retAddBook['data'], 'Array key "data->book" does not exist');
        $this->assertArrayHasKey('id', $retAddBook['data']['book'], 'Array key "data->book->id" does not exist');
        $this->assertIsInt($retAddBook['data']['book']['id'], 'Returned book ID is not an integer');
    }

    public function testCheckoutBookSucess()
    {
        // create book
        $Book = BookModelTest::createTestBook([
            'title'        => 'Harry Potter 101',
            'isbn'         => '8508136110',
            'published_at' => '1950-01-01',
        ]);
        $this->assertTrue($Book->exists(), 'Error creating book');

        // create user
        $User = Users::firstOrCreate(
            ['email' => $this->_userEmail],
            [
                'name'          => $this->_userName,
                'password'      => $this->_userPassword,
                'date_of_birth' => $this->_userBDate,
            ]
        );
        $this->assertTrue($User->exists(), 'Create test user failed');

        // prepare book for checkout
        $Book->status = Books::BOOK_STATUS_AVAILABLE;
        $Book->active = true;
        $retSave      = $Book->save();
        $this->assertTrue($retSave, 'Error editing book');
        
        // checkout book
        $Books = new Books();
        $retCheckout = $Books->checkoutBook($Book->id, $User->id);
        $this->assertIsArray($retCheckout, 'Return is not array');
        $this->assertArrayHasKey('error', $retCheckout);
        $this->assertArrayHasKey('message', $retCheckout);
        $this->assertFalse($retCheckout['error'], 'Checkout returned false');
    }

    public function testCheckinBookSucess()
    {
        // create book
        $Book = BookModelTest::createTestBook([
            'title'        => 'Harry Potter 102',
            'isbn'         => '8508136110',
            'published_at' => '1960-01-01',
        ]);
        $this->assertTrue($Book->exists(), 'Error creating book');

        // create user
        $User = Users::firstOrCreate(
            ['email' => $this->_userEmail],
            [
                'name'          => $this->_userName,
                'password'      => $this->_userPassword,
                'date_of_birth' => $this->_userBDate,
            ]
        );
        $this->assertTrue($User->exists(), 'Create test user failed');

        // prepare book for checkin
        $Book->status = Books::BOOK_STATUS_UNAVAILABLE;
        $retSave      = $Book->save();
        $this->assertTrue($retSave, 'Error editing book');

        // checkin book
        $Books = new Books();
        $retCheckin = $Books->checkinBook($Book->id, $User->id);
        $this->assertIsArray($retCheckin, 'Return is not array');
        $this->assertArrayHasKey('error', $retCheckin);
        $this->assertArrayHasKey('message', $retCheckin);
        $this->assertFalse($retCheckin['error'], 'Checkin returned false');
    }
}