<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use AlizHarb\LaravelHooks\Events\HookExecuted;
use AlizHarb\LaravelHooks\Exceptions\{HookNotFoundException, HookSignatureMismatchException, InvalidCallbackException};
use AlizHarb\LaravelHooks\Jobs\ProcessHookJob;
use BackedEnum;
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

    /** @var array<int, array<string, callable>> */
    protected array $globalListeners = [];

    /** @var array<string, bool> */
    protected array $gracefulHooks = [];

    /** @var array<string, int> */
    protected array $nestingLevels = [];

    /** @var array<string, bool> */
    protected array $mutedHooks = [];

    /** @var bool */
    protected bool $allMuted = false;

    /** @var bool */
    protected bool $isTransactional = false;

    /** @var bool */
    protected bool $isGraceful = false;

    /** @var bool */
    public bool $isLoaded = false;

    public function __construct(
        protected Container $container,
        protected HookInspector $inspector
    ) {
    }

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
     * @return PendingHookRegistration
     */
    public function addAction(string|BackedEnum $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): PendingHookRegistration
    {
        $this->addFilter($hook, $callback, $priority, $acceptedArgs);

        $hookName = $this->resolveHook($hook);
        $id = $this->buildUniqueId($hookName, $callback, $priority);

        return new PendingHookRegistration($this, $hookName, $id, $priority, false);
    }

    /**
     * Register a callback that fires on any hook execution.
     *
     * @param callable $callback
     * @param int $priority
     * @return PendingHookRegistration
     */
    public function onAny(callable $callback, int $priority = 10): PendingHookRegistration
    {
        return $this->addFilter('*', $callback, $priority);
    }

    /**
     * Set the next hook execution to be graceful.
     *
     * @return self
     */
    public function gracefully(): self
    {
        $this->isGraceful = true;

        return $this;
    }

    /**
     * Set the next hook execution to be transactional.
     *
     * @return self
     */
    public function transactional(): self
    {
        $this->isTransactional = true;

        return $this;
    }

    /**
     * Mute a specific hook.
     *
     * @param string $hook
     * @return self
     */
    public function mute(string $hook): self
    {
        $this->mutedHooks[$this->resolveHook($hook)] = true;

        return $this;
    }

    /**
     * Unmute a specific hook.
     *
     * @param string $hook
     * @return self
     */
    public function unmute(string $hook): self
    {
        unset($this->mutedHooks[$this->resolveHook($hook)]);

        return $this;
    }

    /**
     * Mute all hooks.
     *
     * @return self
     */
    public function silence(): self
    {
        $this->allMuted = true;

        return $this;
    }

    /**
     * Execute a callback without any hooks firing.
     *
     * @param callable $callback
     * @return mixed
     */
    public function withoutHooks(callable $callback): mixed
    {
        $previous = $this->allMuted;
        $this->allMuted = true;

        try {
            return $callback();
        } finally {
            $this->allMuted = $previous;
        }
    }

    /**
     * Register a filter hook.
     *
     * @param string|BackedEnum $hook
     * @param callable|string|array $callback
     * @param int $priority
     * @param int $acceptedArgs
     * @return PendingHookRegistration
     */
    public function addFilter(string|BackedEnum $hook, callable|string|array $callback, int $priority = 10, int $acceptedArgs = 1): PendingHookRegistration
    {
        $hookName = $this->resolveHook($hook);

        if ($hookName === '*') {
            $id = $this->buildUniqueId($hookName, $callback, $priority);
            $this->globalListeners[$priority][$id] = $callback;

            return new PendingHookRegistration($this, $hookName, $id, $priority, true);
        }

        if (str_contains($hookName, '*')) {
            $this->addWildcardFilter($hookName, $callback, $priority, $acceptedArgs);
            $id = $this->buildUniqueId($hookName, $callback, $priority);

            return new PendingHookRegistration($this, $hookName, $id, $priority, true);
        }

        $id = $this->buildUniqueId($hookName, $callback, $priority);

        $this->filters[$hookName][$priority][$id] = [
            'function' => $callback,
            'accepted_args' => $acceptedArgs,
        ];

        unset($this->mergedFilters[$hookName]);

        return new PendingHookRegistration($this, $hookName, $id, $priority, true);
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
     * Get all registered wildcard filters.
     *
     * @return array
     */
    public function getWildcardFilters(): array
    {
        return $this->wildcardFilters;
    }

    /**
     * Get all unique hook names that have listeners.
     *
     * @return array<int, string>
     */
    public function getRegisteredHookNames(): array
    {
        return array_unique(array_merge(
            array_keys($this->filters),
            array_keys($this->wildcardFilters),
            array_keys($this->signatures)
        ));
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
     * @return PendingHookCall
     * @throws InvalidCallbackException
     * @throws HookSignatureMismatchException
     */
    public function doAction(string|BackedEnum $hook, mixed ...$args): PendingHookCall
    {
        $hookName = $this->resolveHook($hook);

        $execution = function () use ($hook, $args) {
            $value = array_shift($args);
            $this->applyFilters($hook, $value, ...$args);
        };

        if ($this->isTransactional) {
            $this->isTransactional = false;
            \Illuminate\Support\Facades\DB::transaction($execution);
        } else {
            $execution();
        }

        return new PendingHookCall($this, $hookName);
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

        if ($this->allMuted || isset($this->mutedHooks[$hookName])) {
            return $value;
        }

        // Loop Protection
        $this->nestingLevels[$hookName] = ($this->nestingLevels[$hookName] ?? 0) + 1;
        if ($this->nestingLevels[$hookName] > config('hooks.max_nesting', 50)) {
            $this->nestingLevels[$hookName]--;

            throw new \RuntimeException("Hook loop detected for [{$hookName}]. Max nesting level reached.");
        }

        try {
            $result = $this->runFilters($hookName, $value, ...$args);
        } finally {
            $this->nestingLevels[$hookName]--;
        }

        return $result;
    }

    /**
     * Run the filters for a hook.
     *
     * @param string $hookName
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    protected function runFilters(string $hookName, mixed $value, mixed ...$args): mixed
    {
        $this->checkDeprecation($hookName);

        $allArgs = [$value, ...$args];
        $this->validateSignature($hookName, $allArgs);

        if (! isset($this->filters[$hookName]) && empty($this->wildcardFilters) && empty($this->globalListeners)) {
            if (config('hooks.strict', false)) {
                throw HookNotFoundException::make($hookName);
            }
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

        if (empty($args)) {
            $args[] = $value;
        } else {
            array_unshift($args, $value);
        }

        $this->inspector->record($hookName, $value, $args);

        // Fire any global listeners
        if (! empty($this->globalListeners)) {
            $globals = $this->globalListeners;
            ksort($globals);
            foreach ($globals as $priority => $listeners) {
                foreach ($listeners as $listener) {
                    call_user_func($listener, $hookName, $args);
                }
            }
        }

        foreach ($callbacksToRun as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                try {
                    if (! is_callable($callback['function'])) {
                        if (is_string($callback['function']) && str_contains($callback['function'], '@')) {
                            $callback['function'] = $this->resolveCallback($callback['function']);
                        } elseif (is_array($callback['function']) && count($callback['function']) === 2 && is_string($callback['function'][0])) {
                            $callback['function'][0] = $this->container->make($callback['function'][0]);
                        }
                    }

                    if (! is_callable($callback['function'])) {
                        throw InvalidCallbackException::notCallable($hookName);
                    }

                    $parameters = array_slice($args, 0, (int) $callback['accepted_args']);
                    $value = call_user_func_array($callback['function'], $parameters);

                    $args[0] = $value;
                } catch (\Throwable $e) {
                    if ($this->isGraceful || $this->shouldBeGraceful($hookName)) {
                        Log::error("Hook [{$hookName}] listener failed gracefully: " . $e->getMessage());

                        continue;
                    }

                    throw $e;
                }
            }
        }

        $this->isGraceful = false;

        event(new HookExecuted(
            $hookName,
            $value,
            $args,
            microtime(true) - (defined('LARAVEL_START') ? LARAVEL_START : microtime(true)),
            memory_get_usage()
        ));

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
        $id = is_string($callback) && ! str_contains($callback, '@') && ! method_exists($this, $callback) && ! function_exists($callback)
            ? $callback
            : $this->buildUniqueId($hook, $callback, $priority);

        if (isset($this->filters[$hook][$priority][$id])) {
            unset($this->filters[$hook][$priority][$id]);
            if (empty($this->filters[$hook][$priority])) {
                unset($this->filters[$hook][$priority]);
            }
            unset($this->mergedFilters[$hook]);

            return true;
        }

        if (isset($this->wildcardFilters[$hook][$priority][$id])) {
            unset($this->wildcardFilters[$hook][$priority][$id]);
            if (empty($this->wildcardFilters[$hook][$priority])) {
                unset($this->wildcardFilters[$hook][$priority]);
            }

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
        if (str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback);

            return [$this->container->make($class), $method];
        }

        return [$this->container->make($callback), '__invoke'];
    }

    /**
     * Set a hook as graceful.
     *
     * @param string $hook
     * @return void
     */
    public function setGraceful(string $hook): void
    {
        $this->gracefulHooks[$hook] = true;
    }

    /**
     * Check if a hook should be graceful.
     *
     * @param string $hook
     * @return bool
     */
    protected function shouldBeGraceful(string $hook): bool
    {
        return $this->gracefulHooks[$hook] ?? config('hooks.graceful_by_default', false);
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

    /**
     * Set filters from an array (used for caching).
     *
     * @param array $filters
     * @return void
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
        $this->isLoaded = true;
    }

    /**
     * Set wildcard filters from an array.
     *
     * @param array $filters
     * @return void
     */
    public function setWildcardFilters(array $filters): void
    {
        $this->wildcardFilters = $filters;
    }
}
