<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use AlizHarb\LaravelHooks\Exceptions\{HookSignatureMismatchException, InvalidCallbackException};
use AlizHarb\LaravelHooks\Jobs\ProcessHookJob;
use BackedEnum;
use Closure;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class HookManager
{
    use Macroable;

    /** @var array<string, array<int, array<string, array{function: callable|string|array, accepted_args: int}>>> */
    protected array $filters = [];

    /** @var array<string, array<int, array<string, array{function: callable|string|array, accepted_args: int}>>> */
    protected array $wildcardFilters = [];

    /** @var array<string, bool> */
    protected array $mergedFilters = [];

    /** @var array<string, array<int, string>> */
    protected array $signatures = [];

    /** @var array<string, array{new: string, version: string}> */
    protected array $deprecations = [];

    /** @var array<string, string> */
    protected array $viewOverrides = [];

    public function __construct(
        protected Container $container,
        protected HookInspector $inspector
    ) {}

    /**
     * Define strict signature for a hook.
     *
     * @param string|BackedEnum $hook
     * @param array<int, string> $signature
     * @return self
     */
    public function define(string|BackedEnum $hook, array $signature): self
    {
        $hookName = $this->resolveHook($hook);
        $this->signatures[$hookName] = $signature;

        return $this;
    }

    /**
     * Mark a hook as deprecated.
     *
     * @param string|BackedEnum $old
     * @param string|BackedEnum $new
     * @param string $version
     * @return self
     */
    public function deprecate(string|BackedEnum $old, string|BackedEnum $new, string $version): self
    {
        $oldHook = $this->resolveHook($old);
        $newHook = $this->resolveHook($new);

        $this->deprecations[$oldHook] = ['new' => $newHook, 'version' => $version];

        return $this;
    }

    /**
     * Register an action hook.
     *
     * @param string|BackedEnum $hook
     * @param callable|string|array $callback
     * @param int $priority
     * @param int $acceptedArgs
     * @return self
     */
    public function addAction(string|BackedEnum $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): self
    {
        return $this->addFilter($hook, $callback, $priority, $acceptedArgs);
    }

    /**
     * Register a filter hook.
     *
     * @param string|BackedEnum $hook
     * @param callable|string|array $callback
     * @param int $priority
     * @param int $acceptedArgs
     * @return self
     */
    public function addFilter(string|BackedEnum $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): self
    {
        $hookName = $this->resolveHook($hook);

        if (str_contains($hookName, '*')) {
            return $this->addWildcardFilter($hookName, $callback, $priority, $acceptedArgs);
        }

        $id = $this->buildUniqueId($hookName, $callback, $priority);

        $this->filters[$hookName][$priority][$id] = [
            'function' => $callback,
            'accepted_args' => $acceptedArgs,
        ];

        unset($this->mergedFilters[$hookName]);

        return $this;
    }

    /**
     * Get all registered filters.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Define a pipeline for a hook.
     *
     * @param string|BackedEnum $hook
     * @return HookPipelineBuilder
     */
    public function pipe(string|BackedEnum $hook): HookPipelineBuilder
    {
        $hookName = $this->resolveHook($hook);
        return new HookPipelineBuilder($this, $hookName);
    }

    /**
     * Override a view with another view.
     *
     * @param string $original
     * @param string $override
     * @return void
     */
    public function overrideView(string $original, string $override): void
    {
        $this->viewOverrides[$original] = $override;
    }

    /**
     * Get the overridden view if exists.
     *
     * @param string $view
     * @return string
     */
    public function getOverriddenView(string $view): string
    {
        return $this->viewOverrides[$view] ?? $view;
    }

    /**
     * Execute an action hook.
     *
     * @param string|BackedEnum $hook
     * @param mixed ...$args
     * @return void
     * @throws InvalidCallbackException
     * @throws HookSignatureMismatchException
     */
    public function doAction(string|BackedEnum $hook, mixed ...$args): void
    {
        $value = array_shift($args);
        $this->applyFilters($hook, $value, ...$args);
    }

    /**
     * Apply a filter hook.
     *
     * @param string|BackedEnum $hook
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     * @throws InvalidCallbackException
     * @throws HookSignatureMismatchException
     */
    public function applyFilters(string|BackedEnum $hook, mixed $value, mixed ...$args): mixed
    {
        $hookName = $this->resolveHook($hook);

        $this->checkDeprecation($hookName);

        $allArgs = [$value, ...$args];
        $this->validateSignature($hookName, $allArgs);

        if (! isset($this->filters[$hookName]) && empty($this->wildcardFilters)) {
            return $value;
        }

        if (! isset($this->mergedFilters[$hookName])) {
            $this->mergedFilters[$hookName] = true;
        }

        $callbacksToRun = $this->filters[$hookName] ?? [];

        if (! empty($this->wildcardFilters)) {
            foreach ($this->wildcardFilters as $pattern => $priorities) {
                if (Str::is($pattern, $hookName)) {
                    foreach ($priorities as $priority => $items) {
                        foreach ($items as $id => $item) {
                            $callbacksToRun[$priority][$id] = $item;
                        }
                    }
                }
            }
        }

        ksort($callbacksToRun);

        if (empty($callbacksToRun)) {
            return $value;
        }

        if (empty($args)) {
            $args[] = $value;
        } else {
            array_unshift($args, $value);
        }

        $this->inspector->record($hookName, $value, $args);

        foreach ($callbacksToRun as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                if (! is_callable($callback['function'])) {
                    if (is_string($callback['function']) && str_contains($callback['function'], '@')) {
                        $callback['function'] = $this->resolveCallback($callback['function']);
                    }
                }

                if (! is_callable($callback['function'])) {
                    throw InvalidCallbackException::notCallable($hookName);
                }

                $parameters = array_slice($args, 0, (int) $callback['accepted_args']);
                $value = call_user_func_array($callback['function'], $parameters);

                $args[0] = $value;
            }
        }

        return $value;
    }

    /**
     * Remove a filter hook.
     *
     * @param string $hook
     * @param callable|string|array $callback
     * @param int $priority
     * @return bool
     */
    public function removeFilter(string $hook, callable|string|array $callback, int $priority = 10): bool
    {
        $id = $this->buildUniqueId($hook, $callback, $priority);

        if (isset($this->filters[$hook][$priority][$id])) {
            unset($this->filters[$hook][$priority][$id]);
            if (empty($this->filters[$hook][$priority])) {
                unset($this->filters[$hook][$priority]);
            }
            unset($this->mergedFilters[$hook]);
            return true;
        }

        return false;
    }

    /**
     * Remove an action hook.
     *
     * @param string $hook
     * @param callable|string|array $callback
     * @param int $priority
     * @return bool
     */
    public function removeAction(string $hook, callable|string|array $callback, int $priority = 10): bool
    {
        return $this->removeFilter($hook, $callback, $priority);
    }

    /**
     * Dispatch an action to the queue.
     *
     * @param string|BackedEnum $hook
     * @param mixed ...$args
     * @return void
     */
    public function queueAction(string|BackedEnum $hook, mixed ...$args): void
    {
        $hookName = $this->resolveHook($hook);
        ProcessHookJob::dispatch($hookName, $args);
    }

    /**
     * Get a scoped hook manager instance.
     *
     * @param mixed $scope
     * @return ScopedHookManager
     */
    public function for(mixed $scope): ScopedHookManager
    {
        return new ScopedHookManager($this, $scope);
    }

    /**
     * Register a wildcard filter.
     *
     * @param string $pattern
     * @param callable|string|array $callback
     * @param int $priority
     * @param int $acceptedArgs
     * @return self
     */
    protected function addWildcardFilter(string $pattern, callable|string|array $callback, int $priority, int $acceptedArgs): self
    {
        $id = $this->buildUniqueId($pattern, $callback, $priority);
        $this->wildcardFilters[$pattern][$priority][$id] = [
            'function' => $callback,
            'accepted_args' => $acceptedArgs,
        ];
        return $this;
    }

    /**
     * Validate arguments against signature.
     *
     * @param string $hook
     * @param array $args
     * @return void
     * @throws HookSignatureMismatchException
     */
    protected function validateSignature(string $hook, array $args): void
    {
        if (! isset($this->signatures[$hook])) {
            return;
        }

        foreach ($this->signatures[$hook] as $index => $type) {
            if (! array_key_exists($index, $args)) {
                continue;
            }

            $value = $args[$index];
            $valid = match($type) {
                'string' => is_string($value),
                'int' => is_int($value),
                'float' => is_float($value),
                'bool' => is_bool($value),
                'array' => is_array($value),
                'object' => is_object($value),
                'null' => is_null($value),
                default => $value instanceof $type,
            };

            if (! $valid) {
                throw HookSignatureMismatchException::make($hook, $index, $index, $type);
            }
        }
    }

    /**
     * Check for deprecation and log warning.
     *
     * @param string $hook
     * @return void
     */
    protected function checkDeprecation(string $hook): void
    {
        if (isset($this->deprecations[$hook])) {
            $info = $this->deprecations[$hook];
            Log::warning("Hook [{$hook}] is deprecated since version {$info['version']}. Use [{$info['new']}] instead.");
        }
    }

    /**
     * Build a unique ID for a callback.
     *
     * @param string $hook
     * @param callable|string|array $callback
     * @param int $priority
     * @return string
     */
    protected function buildUniqueId(string $hook, callable|string|array $callback, int $priority): string
    {
        if (is_string($callback)) {
            return $callback;
        }

        if (is_object($callback)) {
            return spl_object_hash($callback);
        }

        if (is_array($callback)) {
            if (is_object($callback[0])) {
                return spl_object_hash($callback[0]) . $callback[1];
            }
            return $callback[0] . '::' . $callback[1];
        }

        return md5(json_encode($callback));
    }

    /**
     * Resolve string callback syntax (Class@method).
     *
     * @param string $callback
     * @return array
     */
    protected function resolveCallback(string $callback): array
    {
        return explode('@', $callback);
    }

    /**
     * Resolve hook name from string or Enum.
     *
     * @param string|BackedEnum $hook
     * @return string
     */
    protected function resolveHook(string|BackedEnum $hook): string
    {
        return $hook instanceof BackedEnum ? (string) $hook->value : $hook;
    }
}
