<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorController extends AbstractController
{
    public function error(FlattenException $exception): JsonResponse
    {
        return $this->json(
            [
                'error_code' => $exception->getStatusCode(),
                'error_text' => $exception->getStatusText(),
            ]
        );
    }
}
