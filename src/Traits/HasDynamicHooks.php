<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Traits;

use AlizHarb\LaravelHooks\Facades\Hook;
use Illuminate\Support\Traits\Macroable;

trait HasDynamicHooks
{
    use Macroable {
        __call as macroCall;
        __callStatic as macroCallStatic;
    }

    /**
     * Boot the trait.
     */
    public static function bootHasDynamicHooks(): void
    {
        Hook::doAction('model.booting.'.static::class, static::class);

        static::registerAttributesFromMetadata();
    }

    /**
     * Scan and register dynamic hooks from attributes.
     */
    protected static function registerAttributesFromMetadata(): void
    {
        $reflection = new \ReflectionClass(static::class);
        $builder = Hook::model(static::class);

        // Class-level attributes
        foreach ($reflection->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();

            if ($instance instanceof \AlizHarb\LaravelHooks\Attributes\Model\DynamicRelation) {
                $builder->relation($instance->name, fn ($model) => $model->{$instance->type}($instance->related, $instance->foreignKey, $instance->localKey, ...$instance->args));
            }

            if ($instance instanceof \AlizHarb\LaravelHooks\Attributes\Model\DynamicCast) {
                $builder->cast($instance->attribute, $instance->type);
            }

            if ($instance instanceof \AlizHarb\LaravelHooks\Attributes\Model\DynamicScope) {
                $callback = $instance->callback;

                // If it's a string, we might need a more complex resolution or just bypass
                // But generally users will pass a closure or leave it null.
                if (is_string($callback)) {
                    $builder->scope($instance->name, fn ($query) => $query); // Fallback or implementation specific
                } else {
                    $builder->scope($instance->name, $callback ?? fn ($query) => $query);
                }
            }
        }

        // Method-level attributes
        foreach ($reflection->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();

                if ($instance instanceof \AlizHarb\LaravelHooks\Attributes\Model\DynamicAccessor) {
                    $name = $instance->name ?? $method->getName();
                    $builder->accessor($name, fn ($model) => $model->{$method->getName()}());
                }

                if ($instance instanceof \AlizHarb\LaravelHooks\Attributes\Model\DynamicMutator) {
                    $name = $instance->name ?? $method->getName();
                    $builder->attribute($name, null, function ($model, $value) use ($method) {
                        return $model->{$method->getName()}($value);
                    });
                }
            }
        }
    }

    /**
     * Get an attribute from the model with hook support.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        return Hook::applyFilters(
            'model.'.strtolower(class_basename($this)).".attribute.{$key}",
            $value,
            $this
        );
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        $value = Hook::applyFilters(
            'model.'.strtolower(class_basename($this)).".set_attribute.{$key}",
            $value,
            $this
        );

        return parent::setAttribute($key, $value);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $array = parent::toArray();

        return Hook::applyFilters(
            'model.'.strtolower(class_basename($this)).'.to_array',
            $array,
            $this
        );
    }

    /**
     * Handle dynamic method calls.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Handle dynamic static method calls.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return static::macroCallStatic($method, $parameters);
        }

        return parent::__callStatic($method, $parameters);
    }
}
