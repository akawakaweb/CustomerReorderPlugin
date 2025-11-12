<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\ReorderEligibility\ResponseProcessing;

use Sylius\CustomerReorderPlugin\ReorderEligibility\ReorderEligibilityCheckerResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

final class ReorderEligibilityCheckerResponseProcessor implements ReorderEligibilityCheckerResponseProcessorInterface
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    /** @param array<ReorderEligibilityCheckerResponse> $responses */
    public function process(array $responses): void
    {
        $session = $this->requestStack->getSession();
        assert($session instanceof Session);

        /** @var ReorderEligibilityCheckerResponse $response */
        foreach ($responses as $response) {
            $session->getFlashBag()->add('info', [
                'message' => $response->getMessage(),
                'parameters' => $response->getParameters(),
            ]);
        }
    }
}
