<?php

namespace App\Test;

use App\ApiPlatform\Test\ApiTestCase;
use App\ApiPlatform\Test\Client;
use App\Entity\User;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CustomApiTestCase extends ApiTestCase
{
    protected function createUser(string $email, string $password): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setUsername(substr($email, 0, strrpos($email, '@')));
        $encodedPassword = self::$container->get('security.password_encoder')->encodePassword($user, $password);
        $user->setPassword($encodedPassword);

        $em = self::$container->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function logIn(Client $client, string $email, string $password)
    {
        $client->request('POST', '/login', [
            'json'    => [
                'email'    => $email,
                'password' => $password,
            ],
            'headers' => ['Content-Type' => 'application/json']
        ]);
        $this->assertResponseStatusCodeSame(204);
    }

    /**
     * @throws TransportExceptionInterface
     */
    protected function createUserAndLogin(Client $client, string $email, string $password) {
        $this->createUser($email, $password);
        $this->logIn($client, $email, $password);
    }
}