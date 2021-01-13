<?php
namespace Tests\Unit;
use App\Models\Users;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserModelTest extends TestCase
{
    use DatabaseTransactions;

    // @TODO Sampler: improve this
    private $_userEmail    = 'testing@sampler.io';
    private $_userName     = 'Test Sampler';
    private $_userPassword = 'Sampler123';
    private $_userBDate    = '1985-03-15';

    public function testAddUserSucess()
    {
        $userData = [
            'email'         => $this->_userEmail,
            'name'          => $this->_userName,
            'password'      => $this->_userPassword,
            'date_of_birth' => $this->_userBDate,
        ];
        $Users      = new Users();
        $retAddUser = $Users->addUser($userData);
        
        $this->assertIsArray($retAddUser, 'Return data is not an array');
        $this->assertArrayHasKey('error', $retAddUser, 'Array key "error" does not exist');
        $this->assertArrayHasKey('message', $retAddUser, 'Array key "message" does not exist');
        $this->assertFalse($retAddUser['error'], 'Add user method returned false');

        $this->assertArrayHasKey('data', $retAddUser, 'Array key "data" does not exist');
        $this->assertArrayHasKey('user', $retAddUser['data'], 'Array key "data->user" does not exist');
        $this->assertArrayHasKey('id', $retAddUser['data']['user'], 'Array key "data->user->id" does not exist');
        $this->assertIsInt($retAddUser['data']['user']['id'], 'Returned user ID is not an integer');
    }
}