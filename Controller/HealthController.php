<?php

namespace bitbirddev\OhDearBundle\Controller;

use bitbirddev\OhDearBundle\HealthCheckInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class HealthController
{
    public const OH_DEAR_HEADER = 'oh-dear-health-check-secret';

    public function __construct(
        private readonly HealthCheckInterface $healthChecker,
        private readonly string $secret,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $ohDearHeader = $request->headers->get(self::OH_DEAR_HEADER);
        if ($this->secret !== $ohDearHeader) {
            return new JsonResponse([], Response::HTTP_NOT_FOUND);
        }

        $checkResults = $this->healthChecker->fetchLatestCheckResults();

        return new JsonResponse(
            $checkResults->toJson(),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }
}
