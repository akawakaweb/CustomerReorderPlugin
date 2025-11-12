<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\ReorderEligibility;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\CustomerReorderPlugin\ReorderEligibility\ResponseProcessing\EligibilityCheckerFailureResponses;

final class ItemsOutOfStockEligibilityChecker implements ReorderEligibilityChecker
{
    public function __construct(
        private readonly ReorderEligibilityConstraintMessageFormatterInterface $reorderEligibilityConstraintMessageFormatter,
        private readonly AvailabilityCheckerInterface $availabilityChecker,
    ) {
    }

    /** @return array<ReorderEligibilityCheckerResponse> */
    public function check(OrderInterface $order, OrderInterface $reorder): array
    {
        /** @var array<string> $productsOutOfStock */
        $productsOutOfStock = [];

        /** @var OrderItemInterface $orderItem */
        foreach ($order->getItems()->getValues() as $orderItem) {
            if (null === $orderItem->getVariant()) {
                continue;
            }

            /** @var ProductVariantInterface $productVariant */
            $productVariant = $orderItem->getVariant();
            if (!$this->availabilityChecker->isStockAvailable($productVariant)) {
                $productName = $orderItem->getProductName();
                if (null !== $productName) {
                    $productsOutOfStock[] = $productName;
                }
            }
        }

        if ([] === $productsOutOfStock) {
            return [];
        }

        $eligibilityCheckerResponse = new ReorderEligibilityCheckerResponse();

        $eligibilityCheckerResponse->setMessage(EligibilityCheckerFailureResponses::ITEMS_OUT_OF_STOCK);
        $eligibilityCheckerResponse->setParameters([
            '%order_items%' => $this->reorderEligibilityConstraintMessageFormatter->format($productsOutOfStock),
        ]);

        return [$eligibilityCheckerResponse];
    }
}
