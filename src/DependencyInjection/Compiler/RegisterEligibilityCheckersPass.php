<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\DependencyInjection\Compiler;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Compiler\PrioritizedCompositeServicePass;
use Sylius\CustomerReorderPlugin\ReorderEligibility\CompositeReorderEligibilityChecker;

final class RegisterEligibilityCheckersPass extends PrioritizedCompositeServicePass
{
    public function __construct()
    {
        parent::__construct(
            CompositeReorderEligibilityChecker::class,
            CompositeReorderEligibilityChecker::class,
            'sylius_customer_reorder_plugin.eligibility_checker',
            'addChecker',
        );
    }
}
