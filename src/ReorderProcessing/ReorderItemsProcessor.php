<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\ReorderProcessing;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Order\Modifier\OrderItemQuantityModifierInterface;
use Sylius\Component\Order\Modifier\OrderModifierInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;

final class ReorderItemsProcessor implements ReorderProcessor
{
    public function __construct(
        private readonly OrderItemQuantityModifierInterface $orderItemQuantityModifier,
        private readonly OrderModifierInterface $orderModifier,
        private readonly AvailabilityCheckerInterface $availabilityChecker,
        private readonly FactoryInterface $orderItemFactory,
    ) {
    }

    public function process(OrderInterface $order, OrderInterface $reorder): void
    {
        $orderItems = $order->getItems();

        /** @var OrderItemInterface $orderItem */
        foreach ($orderItems as $orderItem) {
            if (null === $orderItem->getVariant()) {
                continue;
            }
            if (!$this->availabilityChecker->isStockAvailable($orderItem->getVariant())) {
                continue;
            }
            $reorderItemQuantity = 0;

            if (!$this->availabilityChecker->isStockSufficient($orderItem->getVariant(), $orderItem->getQuantity())) {
                $variant = $orderItem->getVariant();
                $onHand = $variant->getOnHand() ?? 0;
                $onHold = $variant->getOnHold() ?? 0;
                $reorderItemQuantity = $onHand - $onHold;
            } else {
                $reorderItemQuantity = $orderItem->getQuantity();
            }

            /** @var OrderItemInterface $newItem */
            $newItem = $this->orderItemFactory->createNew();

            $newItem->setVariant($orderItem->getVariant());
            $newItem->setUnitPrice($orderItem->getUnitPrice());
            $newItem->setProductName($orderItem->getProductName());
            $newItem->setVariantName($orderItem->getVariantName());

            $this->orderItemQuantityModifier->modify($newItem, $reorderItemQuantity);
            $this->orderModifier->addToOrder($reorder, $newItem);
        }
    }
}
