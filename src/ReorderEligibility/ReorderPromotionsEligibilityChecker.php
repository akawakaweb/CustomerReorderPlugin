<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\ReorderEligibility;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PromotionInterface;
use Sylius\CustomerReorderPlugin\ReorderEligibility\ResponseProcessing\EligibilityCheckerFailureResponses;

final class ReorderPromotionsEligibilityChecker implements ReorderEligibilityChecker
{
    public function __construct(
        private readonly ReorderEligibilityConstraintMessageFormatterInterface $reorderEligibilityConstraintMessageFormatter,
    ) {
    }

    /** @return array<ReorderEligibilityCheckerResponse> */
    public function check(OrderInterface $order, OrderInterface $reorder): array
    {
        if (0 === $reorder->getItems()->count()
            || $order->getPromotions()->getValues() === $reorder->getPromotions()->getValues()
        ) {
            return [];
        }

        /** @var array<string> $disabledPromotions */
        $disabledPromotions = [];

        /** @var PromotionInterface $promotion */
        foreach ($order->getPromotions()->getValues() as $promotion) {
            if (!in_array($promotion, $reorder->getPromotions()->getValues(), true)) {
                $promotionName = $promotion->getName();
                if (null !== $promotionName) {
                    $disabledPromotions[] = $promotionName;
                }
            }
        }

        $eligibilityCheckerResponse = new ReorderEligibilityCheckerResponse();

        $eligibilityCheckerResponse->setMessage(EligibilityCheckerFailureResponses::REORDER_PROMOTIONS_CHANGED);
        $eligibilityCheckerResponse->setParameters([
            '%promotion_names%' => $this->reorderEligibilityConstraintMessageFormatter->format($disabledPromotions),
        ]);

        return [$eligibilityCheckerResponse];
    }
}
