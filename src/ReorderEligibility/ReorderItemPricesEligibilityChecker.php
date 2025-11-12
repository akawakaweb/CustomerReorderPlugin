<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\ReorderEligibility;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\CustomerReorderPlugin\ReorderEligibility\ResponseProcessing\EligibilityCheckerFailureResponses;

final class ReorderItemPricesEligibilityChecker implements ReorderEligibilityChecker
{
    public function __construct(
        private readonly ReorderEligibilityConstraintMessageFormatterInterface $reorderEligibilityConstraintMessageFormatter,
    ) {
    }

    /** @return array<ReorderEligibilityCheckerResponse> */
    public function check(OrderInterface $order, OrderInterface $reorder): array
    {
        /** @var array<string, int> $orderProductNamesToTotal */
        $orderProductNamesToTotal = [];
        /** @var array<string, int> $reorderProductNamesToTotal */
        $reorderProductNamesToTotal = [];

        /** @var OrderItemInterface $orderItem */
        foreach ($order->getItems()->getValues() as $orderItem) {
            $orderProductNamesToTotal[$orderItem->getProductName()] = $orderItem->getUnitPrice();
        }

        /** @var OrderItemInterface $reorderItem */
        foreach ($reorder->getItems()->getValues() as $reorderItem) {
            $reorderProductNamesToTotal[$reorderItem->getProductName()] = $reorderItem->getUnitPrice();
        }

        /** @var array<string> $orderItemsWithChangedPrice */
        $orderItemsWithChangedPrice = [];

        foreach (array_keys($orderProductNamesToTotal) as $productName) {
            if (!array_key_exists($productName, $reorderProductNamesToTotal)) {
                continue;
            }

            if ($orderProductNamesToTotal[$productName] !== $reorderProductNamesToTotal[$productName]) {
                $orderItemsWithChangedPrice[] = $productName;
            }
        }

        if ([] === $orderItemsWithChangedPrice) {
            return [];
        }

        $eligibilityCheckerResponse = new ReorderEligibilityCheckerResponse();

        $eligibilityCheckerResponse->setMessage(EligibilityCheckerFailureResponses::REORDER_ITEMS_PRICES_CHANGED);
        $eligibilityCheckerResponse->setParameters([
            '%product_names%' => $this->reorderEligibilityConstraintMessageFormatter->format($orderItemsWithChangedPrice),
        ]);

        return [$eligibilityCheckerResponse];
    }
}
