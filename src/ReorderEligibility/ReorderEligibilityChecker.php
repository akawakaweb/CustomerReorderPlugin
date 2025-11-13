<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\ReorderEligibility;

use Sylius\Component\Core\Model\OrderInterface;

interface ReorderEligibilityChecker
{
    /** @return array<ReorderEligibilityCheckerResponse> */
    public function check(OrderInterface $order, OrderInterface $reorder): array;
}
