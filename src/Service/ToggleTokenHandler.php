<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class ToggleTokenHandler
{
    private $tokenManager;

    public function __construct(CsrfTokenManagerInterface $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    public function checkToken(Request $request): bool
    {
        return $this->check($request, 'request') || $this->check($request, 'query');
    }

    private function check($request, $method): bool
    {
        $taskId = $request->attributes->get('id');

        if (
            !$request->$method->get('_token')
            || $this->tokenManager->getToken('toggle' . $taskId)->getValue() !== $request->$method->get('_token')
        ) {
            return false;
        }

        return true;
    }
}
