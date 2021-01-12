<?php
# https://laravel.com/docs/8.x/http-tests#assert-json-structure
# $response->decodeResponseJson()
# $response->getContent()
namespace Tests\Feature;

use Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookCheckinTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function testBookCheckinProccessOk()
    {
        // first login to get access token
        $loginBody = [
            'email'    => 'cassin.agustin@gmail.com',
            'password' => 'Sampler123',
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
        // ===============================

        // then try to checkin the book
        $checkinHeaders = [
            'Authorization' => 'Bearer ' . ($arrLoginResponse['data']['access_token'] ?? ''),
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/x-www-form-urlencoded',
        ];
        $checkinResp = $this->withHeaders($checkinHeaders)
                    ->post('/api/books/checkin/999');
        $checkinResp->assertStatus(200)
                    ->assertJsonStructure(['error', 'message']);
        $arrCheckinResponse = $checkinResp->decodeResponseJson();
        $this->assertFalse($arrCheckinResponse['error'], 'Checkin returned error = true');
        // ============================
        
        dd($checkinResp);

        /*
        $response = $this->call('POST', '/api/login', $body, $headers);
        $response = $this->call('POST', '/api/books/checkin/999');
        $this->assertEquals(Response::HTTP_OK, $response->status());
        dd($response->output());
        */
    }
}
