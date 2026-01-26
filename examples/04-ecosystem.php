<?php

namespace App\Models;

use AlizHarb\LaravelHooks\Traits\HasHooks;
use Illuminate\Database\Eloquent\Model;

/**
 * --------------------------------------------------------------------------
 * ELOQUENT INTEGRATION
 * Using the HasHooks trait for model-specific tags.
 * --------------------------------------------------------------------------
 */
class Post extends Model
{
    use HasHooks;

    public function publish()
    {
        $this->update(['published_at' => now()]);

        // Automatically fires 'model.post.published'
        $this->fieldAction('published');
    }
}


/**
 * --------------------------------------------------------------------------
 * FILAMENT INTEGRATION (v4/v5)
 * Using the InteractsWithHooks trait for modular UIs.
 * --------------------------------------------------------------------------
 */

namespace App\Filament\Resources;

use AlizHarb\LaravelHooks\Traits\InteractsWithHooks;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UserResource extends Resource
{
    use InteractsWithHooks;

    public static function table(Table $table): Table
    {
        return static::applyTableHooks(
            $table->columns([
                // ... core columns
            ])
        );
    }

    public static function schema(Schema $schema): Schema
    {
        return static::applySchemaHooks(
            $schema->components([
                // ... core components
            ])
        );
    }
}

/**
 * EXTERNAL MODULE (Example of how to consume the hooks)
 */
// Hook::addFilter('filament.table.UserResource', function (Table $table) {
//     return $table->prependColumns([
//         Tables\Columns\TextColumn::make('module_data'),
//     ]);
// });
