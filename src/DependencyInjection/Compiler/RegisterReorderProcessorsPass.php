<?php

declare(strict_types=1);

namespace Sylius\CustomerReorderPlugin\DependencyInjection\Compiler;

use Sylius\Bundle\ResourceBundle\DependencyInjection\Compiler\PrioritizedCompositeServicePass;
use Sylius\CustomerReorderPlugin\ReorderProcessing\CompositeReorderProcessor;

final class RegisterReorderProcessorsPass extends PrioritizedCompositeServicePass
{
    public function __construct()
    {
        parent::__construct(
            CompositeReorderProcessor::class,
            CompositeReorderProcessor::class,
            'sylius_customer_reorder_plugin.reorder_processor',
            'addProcessor',
        );
    }
}
