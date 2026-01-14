<?php

use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\Jobs\ProcessHookJob;
use Illuminate\Support\Facades\Queue;
use AlizHarb\LaravelHooks\Tests\TestEnum;

enum TestHook: string {
    case Created = 'user.created';
    case Updated = 'user.updated';
}

test('typed hooks work', function () {
    Hook::addFilter(TestHook::Created, function ($val) {
        return $val . '_created';
    });
    
    $res = Hook::applyFilters(TestHook::Created, 'user');
    expect($res)->toBe('user_created');
});

test('wildcard hooks work', function () {
    Hook::addFilter('post.*', function ($val) {
        return $val . '_wildcard';
    });
    
    $res = Hook::applyFilters('post.saved', 'post');
    expect($res)->toBe('post_wildcard');
});

test('queued actions dispatch job', function () {
    Queue::fake();
    
    Hook::queueAction('email.send', 'test@example.com');
    
    Queue::assertPushed(ProcessHookJob::class, function ($job) {
        return $job->hook === 'email.send' && $job->args[0] === 'test@example.com';
    });
});

test('scoped hooks are isolated', function () {
    $scope1 = new stdClass();
    $scope2 = new stdClass();
    
    Hook::for($scope1)->addFilter('name', fn() => 'scope1');
    Hook::for($scope2)->addFilter('name', fn() => 'scope2');
    
    expect(Hook::for($scope1)->applyFilters('name', 'default'))->toBe('scope1');
    expect(Hook::for($scope2)->applyFilters('name', 'default'))->toBe('scope2');
    // Global shouldn't be affected or should be unrelated
    expect(Hook::applyFilters('name', 'default'))->toBe('default');
});
