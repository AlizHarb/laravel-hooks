<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;

// Setup Eloquent for standalone example
$capsule = new Capsule();
$capsule->addConnection([
    'driver' => 'sqlite',
    'database' => ':memory:',
]);
$capsule->bootEloquent();
$capsule->setAsGlobal();

// Create schema
Capsule::schema()->create('users', function ($table) {
    $table->id();
    $table->string('name');
    $table->string('email');
    $table->timestamps();
});

class User extends Model
{
    protected $guarded = [];
}

// --- Dynamic Extension ---

Hook::model(User::class)
    // Add an accessor
    ->accessor('display_name', fn ($user) => strtoupper($user->name))

    // Add a scope
    ->scope('search', fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))

    // Add an event listener
    ->on('created', function ($user) {
        echo "User created: {$user->email}\n";
    });

// --- Usage ---

$user = User::create([
    'name' => 'ali harb',
    'email' => 'ali@example.com',
]);

echo 'Display Name: '.$user->display_name."\n"; // Outputs: ALI HARB

$found = User::search('ali')->first();
echo 'Found User: '.$found->name."\n";
