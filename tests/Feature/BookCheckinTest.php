<?php
# https://laravel.com/docs/8.x/http-tests#assert-json-structure
# $response->decodeResponseJson()
# $response->getContent()

namespace Tests\Feature;
use App\Models\Users;
use App\Models\Books;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BookCheckinTest extends TestCase
{
    use DatabaseTransactions;

    private $_userEmail    = 'testing@sampler.io';
    private $_userName     = 'Test Sampler';
    private $_userPassword = 'Sampler123';
    private $_userBDate    = '1985-03-15';

    /**
     * Test the correct checkin process
     *
     * @return void
     */
    public function testBookCheckinProcessOk()
    {
        // create a super user
        // is it better to use model function?
        $User         = Users::firstOrCreate(
            ['email' => $this->_userEmail],
            [
                'name'          => $this->_userName,
                'password'      => Users::encryptPassword($this->_userPassword),
                'date_of_birth' => $this->_userBDate,
            ]
        );
        $this->assertTrue($User->exists(), 'Create user returned false');
        $User->password  = Users::encryptPassword($this->_userPassword); # if found User, force its password to be this one
        $User->superuser = true;
        $User->save();
        // ================================

        // first login to get access token
        $loginBody = [
            'email'    => $User->email,
            'password' => $this->_userPassword,
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
        // ===============================

        // get a book id that can be checked in
        // same here = model?
        $Book = Books::firstOrCreate(
            ['status' => 'CHECKED_OUT'],
            [
                'title'        => 'Harry Potter 100',
                'isbn'         => '8508136110',
                'published_at' => '1950-01-01',
            ]
        );
        if($Book->status == 'AVAILABLE')
        {
            // checkout book
            $checkoutHeaders = [
                'Authorization' => "Bearer {$accessToken}",
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/x-www-form-urlencoded',
            ];
            $checkoutResp = $this->withHeaders($checkoutHeaders)
                                    ->post("api/books/checkout/{$Book->id}");
            $checkoutResp->assertStatus(200)
                        ->assertJsonStructure(['error', 'message']);
            $arrCheckoutResponse = $checkoutResp->decodeResponseJson();
            $this->assertFalse($arrCheckoutResponse['error'], 'Checkout returned error = true');
        }
        // ====================================

        // after all that logic, try to checkin the book =|
        $checkinHeaders = [
            'Authorization' => "Bearer {$accessToken}",
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ];
        $checkinResp = $this->withHeaders($checkinHeaders)
                    ->post("/api/books/checkin/{$Book->id}");
        $checkinResp->assertStatus(200)
                    ->assertJsonStructure(['error', 'message']);
        $arrCheckinResponse = $checkinResp->decodeResponseJson();
        $this->assertFalse($arrCheckinResponse['error'], 'Checkin returned error = true');
        // ============================
    }

    public function testBookCheckoutProcessOk()
    {
        $this->assertTrue(true);
    }
}
