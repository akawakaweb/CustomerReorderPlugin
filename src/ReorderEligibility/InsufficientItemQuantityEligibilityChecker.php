<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\ReorderEligibility;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\CustomerReorderPlugin\ReorderEligibility\ResponseProcessing\EligibilityCheckerFailureResponses;

final class InsufficientItemQuantityEligibilityChecker implements ReorderEligibilityChecker
{
    public function __construct(
        private readonly ReorderEligibilityConstraintMessageFormatterInterface $reorderEligibilityConstraintMessageFormatter,
    ) {
    }

    /** @return array<ReorderEligibilityCheckerResponse> */
    public function check(OrderInterface $order, OrderInterface $reorder): array
    {
        /** @var array<string, int> $orderProductNamesToQuantity */
        $orderProductNamesToQuantity = [];
        /** @var array<string, int> $reorderProductNamesToQuantity */
        $reorderProductNamesToQuantity = [];

        /** @var OrderItemInterface $item */
        foreach ($order->getItems()->getValues() as $item) {
            $orderProductNamesToQuantity[$item->getProductName()] = $item->getQuantity();
        }

        /** @var OrderItemInterface $item */
        foreach ($reorder->getItems()->getValues() as $item) {
            $reorderProductNamesToQuantity[$item->getProductName()] = $item->getQuantity();
        }

        /** @var array<string> $insufficientItems */
        $insufficientItems = [];

        foreach (array_keys($orderProductNamesToQuantity) as $productName) {
            if (!array_key_exists($productName, $reorderProductNamesToQuantity)) {
                continue;
            }

            if ($orderProductNamesToQuantity[$productName] > $reorderProductNamesToQuantity[$productName]) {
                $insufficientItems[] = $productName;
            }
        }

        if ([] === $insufficientItems) {
            return [];
        }

        $reorderEligibilityCheckerResponse = new ReorderEligibilityCheckerResponse();

        $reorderEligibilityCheckerResponse->setMessage(EligibilityCheckerFailureResponses::INSUFFICIENT_ITEM_QUANTITY);
        $reorderEligibilityCheckerResponse->setParameters([
            '%order_items%' => $this->reorderEligibilityConstraintMessageFormatter->format($insufficientItems),
        ]);

        return [$reorderEligibilityCheckerResponse];
    }
}
