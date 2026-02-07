<?php

declare(strict_types=1);

namespace Tests\Feature;

use AlizHarb\LaravelHooks\Attributes\Model\DynamicAccessor;
use AlizHarb\LaravelHooks\Attributes\Model\DynamicCast;
use AlizHarb\LaravelHooks\Attributes\Model\DynamicRelation;
use AlizHarb\LaravelHooks\Traits\HasDynamicHooks;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AttributeModelExtensionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('attr_test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('config')->nullable();
            $table->timestamps();
        });

        Schema::create('attr_test_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attr_test_model_id');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function test_can_register_relation_via_attribute(): void
    {
        $model = AttrTestModel::create(['name' => 'Root']);
        AttrTestPost::create(['attr_test_model_id' => $model->id, 'title' => 'Post A']);

        $this->assertCount(1, $model->posts);
        $this->assertEquals('Post A', $model->posts->first()->title);
    }

    public function test_can_register_cast_via_attribute(): void
    {
        $model = AttrTestModel::create([
            'name' => 'Cast Test',
            'config' => ['enabled' => true],
        ]);

        $fetched = AttrTestModel::find($model->id)->refresh();

        $this->assertIsArray($fetched->config);
        $this->assertTrue($fetched->config['enabled']);
    }

    public function test_can_register_accessor_via_attribute(): void
    {
        $model = AttrTestModel::create(['name' => 'Ali']);

        $this->assertEquals('Ali (from attr)', $model->display_name);
    }
}

#[DynamicRelation(name: 'posts', type: 'hasMany', related: AttrTestPost::class, foreignKey: 'attr_test_model_id')]
#[DynamicCast(attribute: 'config', type: 'array')]
#[DynamicAccessor(name: 'display_name')]
class AttrTestModel extends Model
{
    use HasDynamicHooks;

    protected $table = 'attr_test_models';

    protected $guarded = [];

    public function getDisplayNameAttribute()
    {
        return $this->name.' (from attr)';
    }
}

class AttrTestPost extends Model
{
    use HasDynamicHooks;

    protected $table = 'attr_test_posts';

    protected $guarded = [];
}
