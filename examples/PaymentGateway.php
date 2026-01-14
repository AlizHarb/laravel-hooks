<?php

namespace AlizHarb\LaravelHooks\Examples;

use AlizHarb\LaravelHooks\Attributes\HookAction;
use AlizHarb\LaravelHooks\Traits\Hookable;

class PaymentGateway
{
    use Hookable;

    public function process(float $amount): void
    {
        // Allow plugins to modify amount (e.g. fees)
        $amount = $this->filter('payment.amount', $amount);
        
        $this->action('payment.processing', $amount);
        
        // ... processing logic ...
        
        $this->action('payment.completed', $amount);
    }
    
    #[HookAction('payment.completed')]
    public function sendReceipt(float $amount)
    {
        // Send email...
    }
}
