<?php
# https://laravel.com/docs/8.x/http-tests#assert-json-structure
# $response->decodeResponseJson()
# $response->getContent()

namespace Tests\Feature;
use Tests\Unit\BookModelTest;
use App\Models\Users;
use App\Models\Books;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookCheckinTest extends TestCase
{
    use RefreshDatabase;

    private $_userEmail    = 'testing@sampler.io';
    private $_userPassword = 'Sampler123';

    /**
     * Create a super user with the private informations
     *
     * @return Users
     */
    public function createSuperUser()
    {
        $User = factory(Users::class)->create(
            [
                'email'    => $this->_userEmail,
                'password' => Users::encryptPassword($this->_userPassword)
            ]
        );
        $this->assertTrue($User->exists(), 'Create user method returned false');
        $User->password  = Users::encryptPassword($this->_userPassword); # if found User, force its password to be this one
        $User->superuser = true;
        $User->save();

        return $User;
    }

    /**
     * Perform a login operation
     *
     * @param string $email
     * @param string $password
     * @return string $accessToken
     */
    public function logInUser(string $email, string $password)
    {
        $loginBody = [
            'email'    => $email,
            'password' => $password,
        ];
        $loginHeaders = [
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];
        $loginResp = $this->withHeaders($loginHeaders)
                            ->post('/api/login', $loginBody);
        $loginResp->assertStatus(200)
                    ->assertJsonStructure(
                        [
                            'error',
                            'message',
                            'data' => [
                                'access_token',
                                'token_type',
                                'expires_in',
                            ]
                        ]
                    );
        $arrLoginResponse = $loginResp->decodeResponseJson();
        $this->assertFalse($arrLoginResponse['error'], 'Login returned error = true');

        $accessToken = $arrLoginResponse['data']['access_token'] ?? NULL;
        $this->assertIsString($accessToken, 'Invalid access token');

        return $accessToken;
    }

    /**
     * Perform a checkin operation
     *
     * @param integer $bookId
     * @param string $accessToken
     * @return void
     */
    public function checkInBook(int $bookId, string $accessToken)
    {
        $checkinHeaders = [
            'Authorization' => "Bearer {$accessToken}",
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ];
        $checkinResp = $this->withHeaders($checkinHeaders)
                                ->post("api/books/checkin/{$bookId}");
        $checkinResp->assertStatus(200)
                    ->assertJsonStructure(['error', 'message']);
        $arrCheckinResponse = $checkinResp->decodeResponseJson();
        $this->assertFalse($arrCheckinResponse['error'], 'Checkin returned error = true');
    }

    /**
     * Perform a checkout operation
     *
     * @param integer $bookId
     * @param string $accessToken
     * @return void
     */
    public function checkOutBook(int $bookId, string $accessToken)
    {
        $checkoutHeaders = [
            'Authorization' => "Bearer {$accessToken}",
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ];
        $checkoutResp = $this->withHeaders($checkoutHeaders)
                                ->post("api/books/checkout/{$bookId}");
        $checkoutResp->assertStatus(200)
                    ->assertJsonStructure(['error', 'message']);
        $arrCheckoutResponse = $checkoutResp->decodeResponseJson();
        $this->assertFalse($arrCheckoutResponse['error'], 'Checkout returned error = true');
    }

    public function testBookCheckinProcessOk()
    {
        // create a super user
        $User = $this->createSuperUser();

        // first login to get access token
        $accessToken = $this->logInUser($User->email, $this->_userPassword);

        // get a book id that can be checked in
        $Book = factory(Books::class)->create(['isbn' => '8508136110']);
        if($Book->status == Books::BOOK_STATUS_AVAILABLE)
        {
            // checkout book
            $this->checkOutBook($Book->id, $accessToken);
        }
        // ====================================

        // after all that logic, try to checkin the book =|
        $this->checkInBook($Book->id, $accessToken);
    }

    public function testBookCheckoutProcessOk()
    {
        // create a super user
        $User = $this->createSuperUser();

        // first login to get access token
        $accessToken = $this->logInUser($User->email, $this->_userPassword);

        // get a book id that can be checked in
        $Book = factory(Books::class)->create(['isbn' => '8508136110']);
        if($Book->status == Books::BOOK_STATUS_UNAVAILABLE)
        {
            // checkin book
            $this->checkInBook($Book->id, $accessToken);
        }
        // ====================================

        // after all that logic, try to checkout the book =|
        $this->checkOutBook($Book->id, $accessToken);
    }
}
