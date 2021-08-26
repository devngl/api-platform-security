<?php

namespace App\Controller;

use ApiPlatform\Core\Api\IriConverterInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login", methods={"POST"})
     */
    public function login(IriConverterInterface $iriConverter)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->json([
                'error' => 'Invalid login request: check that the content type is application/json'
            ], 400);
        }

        return new Response(null, 204, [
            'location' => $iriConverter->getIriFromItem($this->getUser())
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     * @throws Exception
     */
    public function logout()
    {
        throw new Exception('Should not be reached.');
    }
}