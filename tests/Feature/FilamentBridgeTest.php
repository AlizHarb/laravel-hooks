<?php

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Bridge\FilamentHookBridge;
use AlizHarb\LaravelHooks\Facades\Hook;
use stdClass;

test('filament bridge application methods trigger filters', function () {
    $table = new stdClass();
    $schema = new stdClass();
    $infolist = new stdClass();
    $action = new stdClass();

    Hook::addFilter('filament.table.UserResource', function ($t) {
        $t->filtered = true;

        return $t;
    });
    Hook::addFilter('filament.schema.UserResource', function ($s) {
        $s->filtered = true;

        return $s;
    });
    Hook::addFilter('filament.infolist.UserResource', function ($i) {
        $i->filtered = true;

        return $i;
    });
    Hook::addFilter('filament.action.UserResource', function ($a) {
        $a->filtered = true;

        return $a;
    });

    expect(FilamentHookBridge::applyTableHooks($table, 'UserResource')->filtered)->toBeTrue();
    expect(FilamentHookBridge::applySchemaHooks($schema, 'UserResource')->filtered)->toBeTrue();
    expect(FilamentHookBridge::applyInfolistHooks($infolist, 'UserResource')->filtered)->toBeTrue();
    expect(FilamentHookBridge::applyActionHooks($action, 'UserResource')->filtered)->toBeTrue();
});

test('filament bridge application detects context', function () {
    $table = new class () {
        public function getLivewire()
        {
            return new stdClass();
        }
    };

    Hook::addFilter('filament.table.stdClass', function ($t) {
        $t->autoDetected = true;

        return $t;
    });

    expect(FilamentHookBridge::applyTableHooks($table)->autoDetected)->toBeTrue();
});
