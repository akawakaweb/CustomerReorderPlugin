<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\ReorderEligibility\ResponseProcessing;

use Sylius\CustomerReorderPlugin\ReorderEligibility\ReorderEligibilityCheckerResponse;

interface ReorderEligibilityCheckerResponseProcessorInterface
{
    /** @param array<ReorderEligibilityCheckerResponse> $responses */
    public function process(array $responses): void;
}
