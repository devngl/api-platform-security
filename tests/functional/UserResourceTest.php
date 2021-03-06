<?php

namespace App\Tests\functional;

use App\Entity\User;
use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class UserResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateUser()
    {
        $client = self::createClient();

        $client->request('POST', '/api/users', [
            'json' => [
                'email'    => 'cheeseplease@example.com',
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
        $user   = $this->createUserAndLogin($client, 'user@dev.to', 'foo');

        $client->request('PUT', '/api/users/' . $user->getId(), [
            'json' => ['username' => 'newusername']
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'newusername'
        ]);
    }

    public function testAssertOnlyAdminUsersCanSetRoles()
    {
        $client        = self::createClient();
        $target        = $this->createUser('target@dev.to', 'foo');
        $this->createUserAndLogin($client, 'user@dev.to', 'foo');
        $client->request('PUT', '/api/users/' . $target->getId(), [
            'json' => ['roles' => ['ROLE_ADMIN']]
        ]);
        $target = $this->getEntityManager()->getRepository(User::class)->find($target->getId());
        $this->assertEquals(['ROLE_USER'], $target->getRoles());

        $this->createUserAndLogin($client, 'admin@dev.to', 'foo', ['ROLE_ADMIN']);
        $client->request('PUT', '/api/users/' . $target->getId(), [
            'json' => [
                'roles' => ['ROLE_ADMIN', 'ROLE_VIP']
            ]
        ]);
        $target = $this->getEntityManager()->getRepository(User::class)->find($target->getId());
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_VIP', 'ROLE_USER'], $target->getRoles());
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function testGetUser()
    {
        $client = self::createClient();
        $user   = $this->createUserAndLogin($client, 'angel@dev.to', 'foo', ['ROLE_USER']);

        $phoneNumber = '555.123.4567';
        $user->setPhoneNumber($phoneNumber);
        $em = $this->getEntityManager();
        $em->flush();

        $client->request('GET', '/api/users/' . $user->getId());
        $this->assertJsonContains(['username' => 'angel']);
        $this->assertArrayNotHasKey('phoneNumber', $client->getResponse()->toArray());

        $this->createUserAndLogin($client, 'admin@dev.to', 'foo', ['ROLE_ADMIN']);
        $client->request('GET', '/api/users/' . $user->getId());
        $this->assertArrayHasKey('phoneNumber', $client->getResponse()->toArray());
        $this->assertJsonContains(['phoneNumber' => $phoneNumber]);
    }
}