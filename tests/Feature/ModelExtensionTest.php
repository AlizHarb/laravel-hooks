<?php

declare(strict_types=1);

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ModelExtensionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('extension_test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->boolean('active')->default(true);
            $table->string('slug')->nullable();
            $table->text('options')->nullable();
            $table->timestamps();
        });

        Schema::create('extension_test_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_test_model_id');
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('another_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    public function test_can_register_dynamic_relation(): void
    {
        Hook::model(ExtensionTestModel::class)->relation('posts', function ($model) {
            return $model->hasMany(ExtensionTestPost::class, 'extension_test_model_id');
        });

        $model = ExtensionTestModel::create(['name' => 'Test']);
        ExtensionTestPost::create(['extension_test_model_id' => $model->id, 'title' => 'Post 1']);

        $this->assertCount(1, $model->posts);
        $this->assertInstanceOf(ExtensionTestPost::class, $model->posts->first());
    }

    public function test_can_register_dynamic_scope(): void
    {
        Hook::model(ExtensionTestModel::class)->scope('activeOnly', function ($query) {
            return $query->where('active', true);
        });

        ExtensionTestModel::create(['name' => 'Active', 'active' => true]);
        ExtensionTestModel::create(['name' => 'Inactive', 'active' => false]);

        $results = ExtensionTestModel::query()->activeOnly()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Active', $results->first()->name);
    }

    public function test_dynamic_scope_isolation_between_models(): void
    {
        Hook::model(ExtensionTestModel::class)->scope('onlyA', function ($query) {
            return $query->where('name', 'A');
        });

        Hook::model(AnotherModel::class)->scope('onlyA', function ($query) {
            return $query->where('name', 'Different');
        });

        ExtensionTestModel::create(['name' => 'A']);
        AnotherModel::create(['name' => 'A']);
        AnotherModel::create(['name' => 'Different']);

        $this->assertCount(1, ExtensionTestModel::query()->onlyA()->get());
        $this->assertCount(1, AnotherModel::query()->onlyA()->get());
        $this->assertEquals('Different', AnotherModel::query()->onlyA()->first()->name);
    }

    public function test_can_register_dynamic_model_event(): void
    {
        $flag = false;
        Hook::model(ExtensionTestModel::class)->on('created', function ($model) use (&$flag) {
            $flag = true;
        });

        ExtensionTestModel::create(['name' => 'Event Test']);

        $this->assertTrue($flag);
    }

    public function test_can_register_dynamic_global_scope(): void
    {
        Hook::model(ExtensionTestModel::class)->globalScope('onlyNamedABC', function ($query) {
            return $query->where('name', 'ABC');
        });

        ExtensionTestModel::create(['name' => 'ABC']);
        ExtensionTestModel::create(['name' => 'XYZ']);

        $this->assertCount(1, ExtensionTestModel::all());
        $this->assertEquals('ABC', ExtensionTestModel::first()->name);

        // Verify it doesn't affect other models
        AnotherModel::create(['name' => 'XYZ']);
        $this->assertCount(1, AnotherModel::all());
    }

    public function test_can_register_dynamic_cast(): void
    {
        // Insert raw data first
        Schema::getConnection()->table('extension_test_models')->insert([
            'name' => 'Raw Test',
            'options' => json_encode(['foo' => 'bar']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Hook::model(ExtensionTestModel::class)->cast('options', 'array');

        $fetched = ExtensionTestModel::where('name', 'Raw Test')->first();

        $this->assertIsArray($fetched->options);
        $this->assertEquals('bar', $fetched->options['foo']);
    }

    public function test_can_register_dynamic_accessor(): void
    {
        Hook::model(ExtensionTestModel::class)->accessor('computed_name', function ($model) {
            return 'Computed: '.$model->name;
        });

        $model = ExtensionTestModel::create(['name' => 'Original']);

        $this->assertEquals('Computed: Original', $model->computed_name);

        $fetched = ExtensionTestModel::find($model->id);
        $this->assertEquals('Computed: Original', $fetched->computed_name);
    }

    public function test_can_register_dynamic_mutator(): void
    {
        Hook::model(ExtensionTestModel::class)->attribute('slug', null, function ($model, $value) {
            $model->name = \Illuminate\Support\Str::slug($value);
            unset($model->slug);
        });

        $model = new ExtensionTestModel();
        $model->slug = 'Hello World';
        $model->save();

        $this->assertEquals('hello-world', $model->name);
    }

    public function test_can_register_dynamic_hidden(): void
    {
        Hook::model(ExtensionTestModel::class)->hidden(['name']);

        $model = ExtensionTestModel::create(['name' => 'Secret']);
        $fetched = ExtensionTestModel::find($model->id);

        $this->assertArrayNotHasKey('name', $fetched->toArray());
    }

    public function test_can_register_dynamic_fillable(): void
    {
        Hook::model(ExtensionTestModel::class)->fillable(['dynamic_field']);

        $model = ExtensionTestModel::create(['name' => 'Attempt']);
        $fetched = ExtensionTestModel::find($model->id);

        $this->assertContains('dynamic_field', $fetched->getFillable());
    }
}

if (! class_exists(ExtensionTestModel::class)) {
    class ExtensionTestModel extends Model
    {
        use \AlizHarb\LaravelHooks\Traits\HasDynamicHooks;

        protected $table = 'extension_test_models';

        protected $guarded = [];
    }
}

if (! class_exists(AnotherModel::class)) {
    class AnotherModel extends Model
    {
        use \AlizHarb\LaravelHooks\Traits\HasDynamicHooks;

        protected $table = 'another_models';

        protected $guarded = [];
    }
}

if (! class_exists(ExtensionTestPost::class)) {
    class ExtensionTestPost extends Model
    {
        protected $table = 'extension_test_posts';

        protected $guarded = [];
    }
}
