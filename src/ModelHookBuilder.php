<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use Closure;
use Illuminate\Database\Eloquent\Relations\Relation;

class ModelHookBuilder
{
    protected HookManager $manager;
    protected string $modelClass;

    public function __construct(HookManager $manager, string $modelClass)
    {
        $this->manager = $manager;
        $this->modelClass = $modelClass;
    }

    /**
     * Get the hook name for this model (standardized).
     */
    protected function getHookName(): string
    {
        return strtolower(class_basename($this->modelClass));
    }

    /**
     * Register a dynamic relation for the model.
     *
     * @return $this
     */
    public function relation(string $name, Closure $callback): self
    {
        $hookName = $this->getHookName();

        $this->manager->addFilter("model.{$hookName}.relation.{$name}", function ($value, $model = null) use ($callback) {
            return $callback($model);
        }, 10, 2);

        // Add filter to attribute too, so $model->posts works even if not loaded/declared
        $this->manager->addFilter("model.{$hookName}.attribute.{$name}", function ($value, $model = null) use ($name, $callback) {
            if ($model && $model->relationLoaded($name)) {
                return $model->getRelation($name);
            }

            if (! $model) {
                return $value;
            }

            // Simple recursion guard for specific relation
            $relation = $callback($model);

            if ($relation instanceof Relation) {
                return $relation->getResults();
            }

            return $relation;
        }, 10, 2);

        // Register a macro on the model to handle the relation call (method level)
        ($this->modelClass)::macro($name, function () use ($callback) {
            /* @var \Illuminate\Database\Eloquent\Model|static $this */
            return $callback($this);
        });

        return $this;
    }

    /**
     * Register a dynamic scope for the model.
     *
     * @return $this
     */
    public function scope(string $name, Closure $callback): self
    {
        $this->manager->registerDynamicScope($this->modelClass, $name, $callback);

        return $this;
    }

    /**
     * Register a dynamic global scope for the model.
     *
     * @return $this
     */
    public function globalScope(string $name, Closure $callback): self
    {
        ($this->modelClass)::addGlobalScope($name, $callback);

        return $this;
    }

    /**
     * Register a dynamic model event listener.
     *
     * @return $this
     */
    public function on(string $event, Closure $callback): self
    {
        ($this->modelClass)::$event($callback);

        return $this;
    }

    /**
     * Register a dynamic cast for the model.
     *
     * @return $this
     */
    public function cast(string $column, string $cast): self
    {
        $this->manager->addFilter("model.{$this->getHookName()}.casts", function ($casts) use ($column, $cast) {
            $casts[$column] = $cast;

            return $casts;
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
        $this->manager->addFilter("model.{$this->getHookName()}.attribute.{$name}", function ($value, $model = null) use ($callback) {
            return $callback($model);
        }, 10, 2);

        return $this;
    }

    /**
     * Register a dynamic attribute (accessor/mutator) for the model.
     *
     * @return $this
     */
    public function attribute(string $name, ?Closure $get = null, ?Closure $set = null): self
    {
        $hookName = $this->getHookName();

        if ($get) {
            $this->manager->addFilter("model.{$hookName}.attribute.{$name}", function ($value, $model = null) use ($get) {
                return $get($model, $value);
            }, 10, 2);
        }

        if ($set) {
            $this->manager->addFilter("model.{$hookName}.set_attribute.{$name}", function ($value, $model = null) use ($set) {
                return $set($model, $value);
            }, 10, 2);
        }

        return $this;
    }

    /**
     * Register a dynamic method for the model.
     *
     * @return $this
     */
    public function method(string $name, Closure $callback): self
    {
        ($this->modelClass)::macro($name, $callback);

        return $this;
    }

    /**
     * Register fillable attributes for the model.
     *
     * @return $this
     */
    public function fillable(array $attributes): self
    {
        $this->on('retrieved', function ($model) use ($attributes) {
            $model->mergeFillable($attributes);
        });

        return $this;
    }

    /**
     * Register guarded attributes for the model.
     *
     * @return $this
     */
    public function guarded(array $attributes): self
    {
        $this->on('retrieved', function ($model) use ($attributes) {
            $model->mergeGuarded($attributes);
        });

        return $this;
    }

    /**
     * Register visible attributes for the model.
     *
     * @return $this
     */
    public function visible(array $attributes): self
    {
        $this->on('retrieved', function ($model) use ($attributes) {
            $model->setVisible(array_merge($model->getVisible(), $attributes));
        });

        return $this;
    }

    /**
     * Register hidden attributes for the model.
     *
     * @return $this
     */
    public function hidden(array $attributes): self
    {
        $this->on('retrieved', function ($model) use ($attributes) {
            $model->setHidden(array_merge($model->getHidden(), $attributes));
        });

        return $this;
    }
}
