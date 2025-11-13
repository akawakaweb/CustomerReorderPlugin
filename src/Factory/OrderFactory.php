<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\Factory;

use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\CustomerReorderPlugin\ReorderProcessing\ReorderProcessor;
use Webmozart\Assert\Assert;

final class OrderFactory implements OrderFactoryInterface
{
    public function __construct(
        private readonly FactoryInterface $baseOrderFactory,
        private readonly ReorderProcessor $reorderProcessor,
    ) {
    }

    public function createFromExistingOrder(OrderInterface $order, ChannelInterface $channel): OrderInterface
    {
        $reorder = $this->baseOrderFactory->createNew();
        Assert::isInstanceOf($reorder, OrderInterface::class);

        $reorder->setChannel($channel);
        $this->reorderProcessor->process($order, $reorder);

        return $reorder;
    }
}
