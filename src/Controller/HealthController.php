<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Controller;

use bitbirddev\OhDearBundle\Contracts\HealthCheckerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class HealthController
{
    public const OH_DEAR_HEADER = 'oh-dear-health-check-secret';

    public function __construct(
        private readonly HealthCheckerInterface $healthChecker,

        #[Autowire(param: 'oh_dear.secret')]
        private readonly string $secret,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $ohDearHeader = $request->headers->get(self::OH_DEAR_HEADER);
        if ($this->secret !== $ohDearHeader) {
            return new JsonResponse([], Response::HTTP_UNAUTHORIZED);
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
