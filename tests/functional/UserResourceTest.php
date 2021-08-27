<?php

namespace App\Tests\functional;

use ApiPlatform\Core\Tests\Annotation\ApiResourceTest;
use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class UserResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateUser()
    {
        $client = self::createClient();

        $client->request('POST','/api/users', [
            'json' => [
                'email' => 'cheeseplease@example.com',
                'username' => 'cheeseplease',
                'password' => 'brie'
            ],
        ]);
        $this->assertResponseStatusCodeSame(201);

        $this->logIn($client, 'cheeseplease@example.com', 'brie');
    }

    public function testUpdateUser()
    {
        $client = self::createClient();
        $user = $this->createUserAndLogin($client, 'user@dev.to', 'foo');

        $client->request('PUT', '/api/users/'.$user->getId(), [
            'json' => ['username' => 'newusername']
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'newusername'
        ]);
    }
}