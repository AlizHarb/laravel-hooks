<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use Closure;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Scope;

class ModelHookBuilder
{
    public function __construct(
        protected HookManager $manager,
        protected string $modelClass
    ) {}

    /**
     * Register a dynamic relation on the model.
     *
     * @return $this
     */
    public function relation(string $name, Closure $callback): self
    {
        if (method_exists($this->modelClass, 'resolveRelationUsing')) {
            ($this->modelClass)::resolveRelationUsing($name, $callback);
        }

        return $this;
    }

    /**
     * Register a dynamic scope on the model.
     *
     * @return $this
     */
    public function scope(string $name, Closure $callback): self
    {
        $this->manager->registerDynamicScope($this->modelClass, $name, $callback);

        return $this;
    }

    /**
     * Register a model event listener.
     *
     * @return $this
     */
    public function on(string $event, Closure $callback): self
    {
        if (method_exists($this->modelClass, $event)) {
            ($this->modelClass)::$event($callback);
        }

        return $this;
    }

    /**
     * Register a dynamic global scope on the model.
     *
     * @return $this
     */
    public function globalScope(string $name, Closure|Scope $scope): self
    {
        if (method_exists($this->modelClass, 'addGlobalScope')) {
            ($this->modelClass)::addGlobalScope($name, $scope);
        }

        return $this;
    }

    /**
     * Register a dynamic cast on the model.
     *
     * @return $this
     */
    public function cast(string $column, string $cast): self
    {
        $handler = function ($model) use ($column, $cast) {
            $model->mergeCasts([$column => $cast]);
        };

        ($this->modelClass)::retrieved($handler);

        ($this->modelClass)::saving(function ($model) use ($column, $cast) {
            $model->mergeCasts([$column => $cast]);

            // If the model is being saved and have the attribute as an array,
            // but Laravel hasn't processed the cast yet (common with dynamic casts),
            // we might need to force it.
        });

        return $this;
    }

    /**
     * Register a dynamic accessor for the model.
     *
     * @return $this
     */
    public function accessor(string $name, Closure $callback): self
    {
        $handler = function ($model) use ($name, $callback) {
            $model->setRelation($name, $callback($model));
        };

        ($this->modelClass)::retrieved($handler);
        ($this->modelClass)::created($handler);
        ($this->modelClass)::saving($handler);

        return $this;
    }

    /**
     * Register a dynamic attribute (accessor/mutator) for the model.
     * Note: This works by hooking into the retrieved instance.
     *
     * @return $this
     */
    public function attribute(string $name, ?Closure $get = null, ?Closure $set = null): self
    {
        if ($get) {
            $this->accessor($name, $get);
        }

        if ($set) {
            $this->on('saving', function ($model) use ($name, $set) {
                if (isset($model->$name)) {
                    $set($model, $model->$name);
                }
            });
        }

        return $this;
    }

    /**
     * Register a dynamic method on the model.
     * Requires the model to use the HasDynamicHooks trait (or Macroable).
     *
     * @return $this
     */
    public function method(string $name, Closure $callback): self
    {
        if (method_exists($this->modelClass, 'macro')) {
            ($this->modelClass)::macro($name, $callback);
        }

        return $this;
    }

    /**
     * Add fillable attributes to the model.
     *
     * @return $this
     */
    public function fillable(array $attributes): self
    {
        $this->on('retrieved', fn ($model) => $model->mergeFillable($attributes));
        $this->on('saving', fn ($model) => $model->mergeFillable($attributes));

        return $this;
    }

    /**
     * Add guarded attributes to the model.
     *
     * @return $this
     */
    public function guarded(array $attributes): self
    {
        $this->on('retrieved', fn ($model) => $model->mergeGuarded($attributes));
        $this->on('saving', fn ($model) => $model->mergeGuarded($attributes));

        return $this;
    }

    /**
     * Add hidden attributes to the model serialization.
     *
     * @return $this
     */
    public function hidden(array $attributes): self
    {
        $this->on('retrieved', fn ($model) => $model->makeHidden($attributes));

        return $this;
    }

    /**
     * Add visible attributes to the model serialization.
     *
     * @return $this
     */
    public function visible(array $attributes): self
    {
        $this->on('retrieved', fn ($model) => $model->makeVisible($attributes));

        return $this;
    }
}
