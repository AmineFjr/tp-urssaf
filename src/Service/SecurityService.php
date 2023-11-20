<?php

namespace App\Service;

use PHPUnit\Util\Json;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityService
{
    public function login(Request $request): bool
    {
        $authorizationKey = "Basic " . base64_encode($_ENV["LOGIN_USERNAME"]. ":" .$_ENV["LOGIN_PASSWORD"]);

        if ($request->headers->get('Authorization') !== $authorizationKey || !$request->headers->get('Authorization')) {
            return false;
        }

        return true;
    }
}