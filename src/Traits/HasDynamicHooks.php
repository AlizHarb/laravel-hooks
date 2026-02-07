<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Traits;

use AlizHarb\LaravelHooks\Facades\Hook;
use BadMethodCallException;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use ReflectionClass;

trait HasDynamicHooks
{
    use Macroable {
        __call as macroCall;
        __callStatic as macroCallStatic;
    }

    /**
     * Cache for discovered model attributes.
     *
     * @var array<string, bool>
     */
    protected static array $hookMetadataCache = [];

    /**
     * The HookManager instance ID this class was registered with.
     *
     * @var array<string, int>
     */
    protected static array $registeredWithManager = [];

    /**
     * Internal flag to prevent infinite recursion in hooks and magic methods.
     *
     * @var array<string, array<string, bool>>
     */
    protected static array $__hookIsCalling = [];

    public static function bootHasDynamicHooks(): void
    {
        static::ensureAttributesAreRegistered();
    }

    /**
     * Ensure attributes are registered for the current manager instance.
     */
    protected static function ensureAttributesAreRegistered(): void
    {
        $manager = app('hooks');
        $managerId = spl_object_id($manager);

        if (isset(self::$registeredWithManager[static::class]) && self::$registeredWithManager[static::class] === $managerId) {
            return;
        }

        Hook::doAction('model.booting.'.static::class, static::class);

        static::registerAttributesFromMetadata();

        self::$registeredWithManager[static::class] = $managerId;
    }

    /**
     * Scan and register dynamic hooks from attributes.
     */
    protected static function registerAttributesFromMetadata(): void
    {
        $reflection = new ReflectionClass(static::class);
        $builder = Hook::model(static::class);

        // Class-level attributes
        foreach ($reflection->getAttributes() as $attribute) {
            $name = $attribute->getName();

            if (str_ends_with($name, '\\DynamicRelation')) {
                $instance = $attribute->newInstance();
                if ($instance->name) {
                    $builder->relation($instance->name, fn ($model) => $model->{$instance->type}($instance->related, $instance->foreignKey, $instance->localKey, ...$instance->args));
                }
            }

            if (str_ends_with($name, '\\DynamicCast')) {
                $instance = $attribute->newInstance();
                if ($instance->attribute) {
                    $builder->cast($instance->attribute, $instance->type);
                }
            }

            if (str_ends_with($name, '\\DynamicAccessor')) {
                $instance = $attribute->newInstance();
                if ($instance->name) {
                    $methodName = 'get'.Str::studly($instance->name).'Attribute';
                    if (method_exists(static::class, $methodName)) {
                        $builder->accessor($instance->name, fn ($model) => $model->{$methodName}());
                    } else {
                        $builder->accessor($instance->name, fn ($model) => $model->{$instance->name} ?? null);
                    }
                }
            }

            if (str_ends_with($name, '\\DynamicScope')) {
                $instance = $attribute->newInstance();
                $callback = $instance->callback;

                if (is_string($callback)) {
                    $builder->scope($instance->name, fn ($query) => $query);
                } else {
                    $builder->scope($instance->name, $callback ?? fn ($query) => $query);
                }
            }
        }

        // Property-level attributes
        foreach ($reflection->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();

                if ($instance instanceof \AlizHarb\LaravelHooks\Attributes\Model\DynamicRelation) {
                    $name = $instance->name ?? $property->getName();
                    $builder->relation($name, fn ($model) => $model->{$instance->type}($instance->related, $instance->foreignKey, $instance->localKey, ...$instance->args));
                }

                if ($instance instanceof \AlizHarb\LaravelHooks\Attributes\Model\DynamicCast) {
                    $attr = $instance->attribute ?? $property->getName();
                    $builder->cast($attr, $instance->type);
                }

                if ($instance instanceof \AlizHarb\LaravelHooks\Attributes\Model\DynamicAccessor) {
                    $name = $instance->name ?? $property->getName();
                    $methodName = 'get'.Str::studly($name).'Attribute';
                    if (method_exists(static::class, $methodName)) {
                        $builder->accessor($name, fn ($model) => $model->{$methodName}());
                    } else {
                        $builder->accessor($name, fn ($model) => $model->{$name} ?? null);
                    }
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
     * Get the casts array for the model.
     *
     * @return array
     */
    public function getCasts()
    {
        static::ensureAttributesAreRegistered();

        if (isset(self::$__hookIsCalling[static::class]['get_casts'])) {
            return parent::getCasts();
        }

        self::$__hookIsCalling[static::class]['get_casts'] = true;

        try {
            $casts = parent::getCasts();

            return Hook::applyFilters(
                "model.{$this->getHookName()}.casts",
                $casts,
                $this
            );
        } finally {
            unset(self::$__hookIsCalling[static::class]['get_casts']);
        }
    }

    /**
     * Get the hook name for this model.
     */
    protected function getHookName(): string
    {
        return strtolower(class_basename(static::class));
    }

    /**
     * Get an attribute from the model with hook support.
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        static::ensureAttributesAreRegistered();

        if (isset(self::$__hookIsCalling[static::class]["get_{$key}"])) {
            return parent::getAttribute($key);
        }

        self::$__hookIsCalling[static::class]["get_{$key}"] = true;

        try {
            $value = parent::getAttribute($key);

            return Hook::applyFilters(
                "model.{$this->getHookName()}.attribute.{$key}",
                $value,
                $this
            );
        } finally {
            unset(self::$__hookIsCalling[static::class]["get_{$key}"]);
        }
    }

    /**
     * Set a given attribute on the model.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        static::ensureAttributesAreRegistered();

        if (isset(self::$__hookIsCalling[static::class]["set_{$key}"])) {
            return parent::setAttribute($key, $value);
        }

        self::$__hookIsCalling[static::class]["set_{$key}"] = true;

        try {
            $value = Hook::applyFilters(
                "model.{$this->getHookName()}.set_attribute.{$key}",
                $value,
                $this
            );

            return parent::setAttribute($key, $value);
        } finally {
            unset(self::$__hookIsCalling[static::class]["set_{$key}"]);
        }
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        static::ensureAttributesAreRegistered();

        if (isset(self::$__hookIsCalling[static::class]['to_array'])) {
            return parent::toArray();
        }

        self::$__hookIsCalling[static::class]['to_array'] = true;

        try {
            $array = parent::toArray();

            return Hook::applyFilters(
                "model.{$this->getHookName()}.to_array",
                $array,
                $this
            );
        } finally {
            unset(self::$__hookIsCalling[static::class]['to_array']);
        }
    }

    /**
     * Handle dynamic method calls.
     */
    public function __call($method, $parameters)
    {
        static::ensureAttributesAreRegistered();

        if (isset(self::$__hookIsCalling[static::class][$method])) {
            return parent::__call($method, $parameters);
        }

        self::$__hookIsCalling[static::class][$method] = true;

        try {
            if (static::hasMacro($method)) {
                return $this->macroCall($method, $parameters);
            }

            if (method_exists(parent::class, '__call')) {
                return parent::__call($method, $parameters);
            }

            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.',
                static::class,
                $method
            ));
        } finally {
            unset(self::$__hookIsCalling[static::class][$method]);
        }
    }

    /**
     * Handle dynamic static method calls.
     */
    public static function __callStatic($method, $parameters)
    {
        if (isset(self::$__hookIsCalling[static::class][$method])) {
            return parent::__callStatic($method, $parameters);
        }

        self::$__hookIsCalling[static::class][$method] = true;

        try {
            if (static::hasMacro($method)) {
                return static::macroCallStatic($method, $parameters);
            }

            if (method_exists(parent::class, '__callStatic')) {
                return parent::__callStatic($method, $parameters);
            }

            throw new BadMethodCallException(sprintf(
                'Method %s::%s does not exist.',
                static::class,
                $method
            ));
        } finally {
            unset(self::$__hookIsCalling[static::class][$method]);
        }
    }
}
