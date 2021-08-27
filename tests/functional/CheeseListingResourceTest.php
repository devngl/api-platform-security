<?php

namespace App\Tests\functional;

use App\ApiPlatform\Test\ApiTestCase;
use App\Entity\User;
use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CheeseListingResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    /**
     * @throws TransportExceptionInterface
     */
    public function testCreateCheeseListing()
    {
        $client = self::createClient();

        $client->request('POST', '/api/cheeses', [
            'json' => [],
        ]);

        self::assertResponseStatusCodeSame(401);

        $this->createUserAndLogin($client, 'cheeseplease@example.com', 'foo');
        $client->request('POST', '/api/cheeses', [
            'json' => [],
        ]);
        self::assertResponseStatusCodeSame(400);
    }
}