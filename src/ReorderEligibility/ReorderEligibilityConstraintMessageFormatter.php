<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\ReorderEligibility;

final class ReorderEligibilityConstraintMessageFormatter implements ReorderEligibilityConstraintMessageFormatterInterface
{
    /** @param array<string> $messageParameters */
    public function format(array $messageParameters): string
    {
        $message = '';

        if (1 === count($messageParameters)) {
            return array_pop($messageParameters);
        }

        $lastMessageParameter = end($messageParameters);
        foreach ($messageParameters as $messageParameter) {
            $message .= $messageParameter . (($messageParameter !== $lastMessageParameter) ? ', ' : '');
        }

        return $message;
    }
}
