<?php

declare(strict_types=1);

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Facades\Hook;
use AlizHarb\LaravelHooks\Traits\HasDynamicHooks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HasDynamicHooksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('hookable_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function test_can_add_dynamic_method(): void
    {
        Hook::model(HookableModel::class)->method('sayHello', function () {
            /** @var HookableModel $this */
            return 'Hello from '.$this->name;
        });

        $model = new HookableModel(['name' => 'Ali']);

        $this->assertEquals('Hello from Ali', $model->sayHello());
    }

    public function test_can_hook_into_attributes(): void
    {
        Hook::addFilter('model.hookablemodel.attribute.name', function ($value) {
            return strtoupper($value);
        });

        $model = new HookableModel(['name' => 'ali']);

        $this->assertEquals('ALI', $model->name);
    }

    public function test_can_add_dynamic_static_method(): void
    {
        HookableModel::macro('staticHello', function () {
            return 'Static Hello';
        });

        $this->assertEquals('Static Hello', HookableModel::staticHello());
    }

    public function test_can_filter_to_array(): void
    {
        Hook::addFilter('model.hookablemodel.to_array', function ($array) {
            $array['extra'] = 'foo';

            return $array;
        });

        $model = new HookableModel(['name' => 'Ali']);
        $this->assertArrayHasKey('extra', $model->toArray());
        $this->assertEquals('foo', $model->toArray()['extra']);
    }

    public function test_can_hook_set_attribute(): void
    {
        Hook::addFilter('model.hookablemodel.set_attribute.name', function ($value) {
            return 'Prefix: '.$value;
        });

        $model = new HookableModel;
        $model->name = 'Ali';

        $this->assertEquals('Prefix: Ali', $model->name);
    }
}

class HookableModel extends Model
{
    use HasDynamicHooks;

    protected $table = 'hookable_models';

    protected $guarded = [];
}
