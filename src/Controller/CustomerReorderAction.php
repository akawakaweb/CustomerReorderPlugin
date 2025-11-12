<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\Controller;

use Sylius\Bundle\CoreBundle\Storage\CartSessionStorage;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\CustomerReorderPlugin\Reorder\ReordererInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

final class CustomerReorderAction
{
    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(
        private readonly CartSessionStorage $cartSessionStorage,
        private readonly ChannelContextInterface $channelContext,
        private readonly CustomerContextInterface $customerContext,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ReordererInterface $reorderer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->find($request->attributes->get('id'));

        $channel = $this->channelContext->getChannel();
        Assert::isInstanceOf($channel, ChannelInterface::class);

        /** @var CustomerInterface $customer */
        $customer = $this->customerContext->getCustomer();

        $reorder = null;

        try {
            $reorder = $this->reorderer->reorder($order, $channel, $customer);
        } catch (\InvalidArgumentException $exception) {
            $session = $request->getSession();
            Assert::isInstanceOf($session, Session::class);

            $session->getFlashBag()->add('info', $exception->getMessage());

            return new RedirectResponse($this->urlGenerator->generate('sylius_shop_account_order_index'));
        }

        $this->cartSessionStorage->setForChannel($channel, $reorder);

        return new RedirectResponse($this->urlGenerator->generate('sylius_shop_cart_summary'));
    }
}
