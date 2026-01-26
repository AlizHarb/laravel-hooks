<?php

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Bridge\EloquentHookBridge;
use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

class TestUser extends Model
{
    protected $table = 'users';
}

class TestPost extends Model
{
    protected $table = 'posts';
}

test('eloquent bridge dispatches model-specific hooks', function () {
    Config::set('hooks.eloquent_bridge.enabled', true);

    $bridge = app(EloquentHookBridge::class);
    $bridge->register();

    $capturedHooks = [];
    Hook::onAny(function ($hook) use (&$capturedHooks) {
        $capturedHooks[] = $hook;
    });

    $user = new TestUser();

    // Simulate Eloquent event
    Event::dispatch('eloquent.saved: ' . TestUser::class, [$user]);

    expect($capturedHooks)->toContain('eloquent.saved: ' . TestUser::class)
        ->and($capturedHooks)->toContain('model.testuser.saved: ' . TestUser::class);
});

test('eloquent bridge respects excludes', function () {
    Config::set('hooks.eloquent_bridge.enabled', true);
    Config::set('hooks.eloquent_bridge.except_models', [TestPost::class]);

    $bridge = app(EloquentHookBridge::class);
    $bridge->register();

    $capturedHooks = [];
    Hook::onAny(function ($hook) use (&$capturedHooks) {
        $capturedHooks[] = $hook;
    });

    // Case 1: Allowed model
    Event::dispatch('eloquent.saved: ' . TestUser::class, [new TestUser()]);
    expect($capturedHooks)->toContain('model.testuser.saved: ' . TestUser::class);

    // Case 2: Excluded model
    $capturedHooks = [];
    Event::dispatch('eloquent.saved: ' . TestPost::class, [new TestPost()]);
    expect($capturedHooks)->toBeEmpty();
});
