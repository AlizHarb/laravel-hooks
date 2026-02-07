<?php

use AlizHarb\LaravelHooks\Attributes\Model\DynamicRelation;
use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\Traits\HasDynamicHooks;
use Illuminate\Database\Eloquent\Model;

/**
 * 1. Macroable Attributes (v1.2.0)
 * Declarative model extensions.
 */
#[DynamicRelation(name: 'comments', type: 'hasMany', related: 'App\Models\Comment')]
class Post extends Model
{
    use HasDynamicHooks;
}

/**
 * 2. Type-Safe DTOs (v1.2.0)
 * Define signatures with class names.
 */
class OrderData
{
    public string $id;
}

Hook::define('order.shipped', [OrderData::class]);

// This works:
Hook::doAction('order.shipped', new OrderData());

// This would throw HookSignatureMismatchException:
// Hook::doAction('order.shipped', 'not-a-dto');

/*
 * 3. Circuit Breaker (v1.2.0)
 * failsafe hooks.
 */
Hook::addAction('flaky.service', function () {
    throw new Exception('Service down');
});

// If called 5 times (default), the hook will be automatically muted.
for ($i = 0; $i < 10; $i++) {
    Hook::gracefully()->doAction('flaky.service');
}

if (Hook::isMuted('flaky.service')) {
    echo 'Circuit breaker tripped!';
}

/*
 * 4. Blade Fallbacks (v1.2.0)
 * See docs/integrations.md for @hook('name', 'fallback.view')
 */
