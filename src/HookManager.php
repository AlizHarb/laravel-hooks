<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use AlizHarb\LaravelHooks\Events\HookExecuted;
use AlizHarb\LaravelHooks\Exceptions\HookNotFoundException;
use AlizHarb\LaravelHooks\Exceptions\HookSignatureMismatchException;
use AlizHarb\LaravelHooks\Exceptions\InvalidCallbackException;
use AlizHarb\LaravelHooks\Jobs\ProcessHookJob;
use BackedEnum;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;

class HookManager
{
    use Macroable;

    /** @var array<string, array<int, array<string, array{function: callable|string|array, accepted_args: int, validation?: string|\Closure}>>> */
    protected array $filters = [];

    /** @var array<string, array<int, array<string, array{function: callable|string|array, accepted_args: int, validation?: string|\Closure}>>> */
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

    protected bool $allMuted = false;

    protected bool $isTransactional = false;

    protected bool $isGraceful = false;

    /** @var array<string, array<string, \Closure>> */
    protected array $dynamicScopes = [];

    /** @var array<string, bool> */
    protected array $registeredMacros = [];

    /** @var array<string, array<string, array{name: string, value: mixed, extra: array}>> */
    protected array $virtualEnumCases = [];

    /** @var array<string, mixed> */
    protected array $context = [];

    /** @var array<string, array<string, array{connection: ?string, queue: ?string}>> */
    protected array $queuedListeners = [];

    protected bool $isTracing = false;

    /** @var array<int, array> */
    protected array $traceLogs = [];

    public bool $isLoaded = false;

    /** @var array<string, int> */
    protected array $failureCounts = [];

    /** @var array<string, array<string, float>> */
    protected array $hookStartTimes = [];

    public function __construct(
        protected Container $container,
        protected HookInspector $inspector
    ) {}

    /**
     * Define strict signature for a hook.
     *
     * @param array<int, string> $signature
     */
    public function define(string|BackedEnum $hook, array $signature): self
    {
        $hookName = $this->resolveHook($hook);
        $this->signatures[$hookName] = $signature;

        return $this;
    }

    /**
     * Mark a hook as deprecated.
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
     */
    public function onAny(callable $callback, int $priority = 10): PendingHookRegistration
    {
        return $this->addFilter('*', $callback, $priority);
    }

    /**
     * Set the next hook execution to be graceful.
     */
    public function gracefully(): self
    {
        $this->isGraceful = true;

        return $this;
    }

    /**
     * Set the next hook execution to be transactional.
     */
    public function transactional(): self
    {
        $this->isTransactional = true;

        return $this;
    }

    /**
     * Mute a specific hook.
     */
    public function mute(string $hook): self
    {
        $this->mutedHooks[$this->resolveHook($hook)] = true;

        return $this;
    }

    /**
     * Unmute a specific hook.
     */
    public function unmute(string $hook): self
    {
        unset($this->mutedHooks[$this->resolveHook($hook)]);

        return $this;
    }

    /**
     * Mute all hooks.
     */
    public function silence(): self
    {
        $this->allMuted = true;

        return $this;
    }

    /**
     * Execute a callback without any hooks firing.
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

    public function validateListener(string $hook, string $id, int $priority, string|\Closure $rules): void
    {
        $this->filters[$hook][$priority][$id]['validation'] = $rules;
    }

    /**
     * Check if a hook has any active listeners.
     */
    public function hasListeners(string|BackedEnum $hook): bool
    {
        $hookName = $this->resolveHook($hook);

        return isset($this->filters[$hookName]) || isset($this->wildcardFilters[$hookName]);
    }

    /**
     * Get all registered filters.
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Get all registered wildcard filters.
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
     */
    public function pipe(string|BackedEnum $hook): HookPipelineBuilder
    {
        $hookName = $this->resolveHook($hook);

        return new HookPipelineBuilder($this, $hookName);
    }

    /**
     * Override a view with another view.
     */
    public function overrideView(string $original, string $override): void
    {
        $this->viewOverrides[$original] = $override;
    }

    /**
     * Get the overridden view if exists.
     */
    public function getOverriddenView(string $view): string
    {
        return $this->viewOverrides[$view] ?? $view;
    }

    /**
     * Execute an action hook.
     *
     * @throws InvalidCallbackException
     * @throws HookSignatureMismatchException
     */
    public function doAction(string|BackedEnum $hook, mixed ...$args): mixed
    {
        $hookName = $this->resolveHook($hook);
        $value = $args[0] ?? null;

        $execution = function () use ($hook, &$args) {
            $val = array_shift($args);

            return $this->applyFilters($hook, $val, ...$args);
        };

        if ($this->isTransactional) {
            $this->isTransactional = false;
            $value = \Illuminate\Support\Facades\DB::transaction($execution);
        } else {
            $value = $execution();
        }

        return $value;
    }

    /**
     * Render a hook action, supporting fallbacks for Blade.
     */
    public function renderAction(string|BackedEnum $hook, mixed ...$args): ?string
    {
        $hookName = $this->resolveHook($hook);
        $fallback = null;

        // If the first argument after hook is a string and potentially a view
        // And we have no listeners, we treat it as a fallback.
        if (count($args) > 0 && is_string($args[0]) && ! $this->hasListeners($hookName)) {
            $fallback = array_shift($args);
            if (\Illuminate\Support\Facades\View::exists($fallback)) {
                return (string) \Illuminate\Support\Facades\View::make($fallback, $args[0] ?? [], $args[1] ?? [])->render();
            }
            // Put it back if it doesn't look like a view
            array_unshift($args, $fallback);
        }

        $result = $this->doAction($hook, ...$args);

        return is_string($result) ? $result : null;
    }

    /**
     * Apply a filter hook.
     *
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
            foreach ($callbacks as $id => $callback) {
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

                    // Signature Enforcement (Premium Safety)
                    if (config('hooks.strict_mode', false)) {
                        $this->enforceStrictSignature($hookName, $callback['function'], (int) $callback['accepted_args'], count($args));
                    }

                    if (isset($this->queuedListeners[$hookName][$id])) {
                        $config = $this->queuedListeners[$hookName][$id];
                        $job = new \AlizHarb\LaravelHooks\Jobs\HookJob($hookName, $callback['function'], $args);

                        if ($config['connection']) {
                            $job->onConnection($config['connection']);
                        }

                        if ($config['queue']) {
                            $job->onQueue($config['queue']);
                        }

                        dispatch($job);

                        continue;
                    }

                    $parameters = array_slice($args, 0, (int) $callback['accepted_args']);
                    $before = is_scalar($args[0]) ? (string) $args[0] : gettype($args[0]);

                    $value = call_user_func_array($callback['function'], $parameters);

                    // Tracing log
                    if ($this->isTracing) {
                        $after = is_scalar($value) ? (string) $value : gettype($value);
                        if ($before !== $after) {
                            $this->logMutation($hookName, $id, $before, $after);
                        }
                    }

                    // Smart Validation
                    if (isset($callback['validation'])) {
                        if (! $this->validateResult($value, $callback['validation'])) {
                            Log::warning("Hook [{$hookName}] listener [{$id}] returned invalid data. Falling back to previous value.");
                            $value = $args[0]; // Fallback to original value
                        }
                    }

                    $args[0] = $value;
                } catch (\Throwable $e) {
                    $this->failureCounts[$hookName] = ($this->failureCounts[$hookName] ?? 0) + 1;

                    if ($this->failureCounts[$hookName] >= config('hooks.circuit_breaker.threshold', 5)) {
                        $this->mute($hookName);
                        Log::critical("Hook [{$hookName}] tripped its circuit breaker after ".config('hooks.circuit_breaker.threshold', 5).' failures. This hook is now silenced.');
                    }

                    if ($this->isGraceful || $this->shouldBeGraceful($hookName)) {
                        Log::error("Hook [{$hookName}] listener failed gracefully: ".$e->getMessage());

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
     */
    public function removeAction(string $hook, callable|string|array $callback, int $priority = 10): bool
    {
        return $this->removeFilter($hook, $callback, $priority);
    }

    /**
     * Dispatch an action to the queue.
     */
    public function queueAction(string|BackedEnum $hook, mixed ...$args): void
    {
        $hookName = $this->resolveHook($hook);
        ProcessHookJob::dispatch($hookName, $args);
    }

    /**
     * Get a scoped hook manager instance.
     */
    public function for(mixed $scope): ScopedHookManager
    {
        return new ScopedHookManager($this, $scope);
    }

    /**
     * Start a model extension builder.
     */
    public function model(string $modelClass): ModelHookBuilder
    {
        return new ModelHookBuilder($this, $modelClass);
    }

    /**
     * Start an enum extension builder.
     */
    public function enum(string $enumClass): EnumHookBuilder
    {
        return new EnumHookBuilder($this, $enumClass);
    }

    /**
     * Register a dynamic case for an enum.
     */
    public function registerEnumCase(string $enumClass, string $name, mixed $value, array $extra = []): void
    {
        $this->virtualEnumCases[$enumClass][$name] = [
            'name' => $name,
            'value' => $value,
            'extra' => $extra,
        ];
    }

    /**
     * Get dynamic cases for an enum.
     */
    public function getEnumCases(string $enumClass): array
    {
        return $this->virtualEnumCases[$enumClass] ?? [];
    }

    /**
     * Mark a listener to be executed on a queue.
     */
    public function markAsQueued(string $hook, string $id, ?string $connection = null, ?string $queue = null): void
    {
        $this->queuedListeners[$hook][$id] = [
            'connection' => $connection,
            'queue' => $queue,
        ];
    }

    /**
     * Validate a result against rules.
     */
    protected function validateResult(mixed $value, string|\Closure $rules): bool
    {
        if ($rules instanceof \Closure) {
            return (bool) $rules($value);
        }

        $validator = \Illuminate\Support\Facades\Validator::make(
            ['value' => $value],
            ['value' => $rules]
        );

        return ! $validator->fails();
    }

    /**
     * Set a context value for the current request cycle.
     */
    public function setContext(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }

    /**
     * Get a context value.
     */
    public function getContext(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }

    /**
     * Check if context has a key.
     */
    public function hasContext(string $key): bool
    {
        return array_key_exists($key, $this->context);
    }

    /**
     * Remove a context key.
     */
    public function forgetContext(string $key): void
    {
        unset($this->context[$key]);
    }

    /**
     * Clear all context.
     */
    public function flushContext(): void
    {
        $this->context = [];
    }

    /**
     * Map a Laravel event to a Hook.
     */
    public function bridge(string $event, string $hook): void
    {
        \Illuminate\Support\Facades\Event::listen($event, function (...$args) use ($hook) {
            // If the first argument is an array and it's the only argument,
            // we check if it's associative. If so, pass it as one item.
            if (count($args) === 1 && is_array($args[0]) && ! array_is_list($args[0])) {
                return $this->doAction($hook, $args[0]);
            }

            return $this->doAction($hook, ...$args);
        });
    }

    /**
     * Enable execution tracing.
     */
    public function enableTracing(): void
    {
        $this->isTracing = true;
    }

    /**
     * Get and clear recent trace logs.
     */
    public function getRecentTraceLogs(): array
    {
        $logs = $this->traceLogs;
        $this->traceLogs = [];

        return $logs;
    }

    protected function logMutation(string $hook, string $listener, string $before, string $after): void
    {
        // Find existing log for this request or create one
        // For simplicity in this demo, we store them in a flat array
        $this->traceLogs[] = [
            'hook' => $hook,
            'time' => round((microtime(true) - \LARAVEL_START) * 1000, 2),
            'mutations' => [[
                'listener' => $listener,
                'before' => $before,
                'after' => $after,
            ]],
        ];
    }

    protected function enforceStrictSignature(string $hook, callable $callback, int $accepted, int $provided): void
    {
        if ($provided < $accepted) {
            throw new HookSignatureMismatchException("Hook [{$hook}] expects {$accepted} arguments, but only {$provided} provided.");
        }
    }

    /**
     * Register a dynamic scope for a model.
     */
    public function registerDynamicScope(string $model, string $name, \Closure $callback): void
    {
        $this->dynamicScopes[$model][$name] = $callback;

        if (! isset($this->registeredMacros[$name])) {
            $this->registeredMacros[$name] = true;

            Builder::macro($name, function (...$args) use ($name) {
                /** @var Builder $this */
                $model = $this->getModel();
                $modelClass = get_class($model);
                $callback = \AlizHarb\LaravelHooks\Facades\Hook::getDynamicScope($modelClass, $name);

                if ($callback) {
                    return $callback($this, ...$args);
                }

                return $this;
            });
        }
    }

    /**
     * Get a registered dynamic scope callback.
     */
    public function getDynamicScope(string $model, string $name): ?\Closure
    {
        return $this->dynamicScopes[$model][$name] ?? null;
    }

    /**
     * Register a wildcard filter.
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
            $valid = match ($type) {
                'string' => is_string($value),
                'int' => is_int($value),
                'float' => is_float($value),
                'bool' => is_bool($value),
                'array' => is_array($value),
                'object' => is_object($value),
                'null' => is_null($value),
                default => class_exists($type) && $value instanceof $type,
            };

            if (! $valid) {
                throw HookSignatureMismatchException::make($hook, $index, $index, $type);
            }
        }
    }

    /**
     * Check for deprecation and log warning.
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
                return spl_object_hash($callback[0]).$callback[1];
            }

            return $callback[0].'::'.$callback[1];
        }

        return md5(json_encode($callback));
    }

    /**
     * Resolve string callback syntax (Class@method).
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
     */
    public function setGraceful(string $hook): void
    {
        $this->gracefulHooks[$hook] = true;
    }

    /**
     * Check if a hook should be graceful.
     */
    protected function shouldBeGraceful(string $hook): bool
    {
        return $this->gracefulHooks[$hook] ?? config('hooks.graceful_by_default', false);
    }

    /**
     * Resolve hook name from string or Enum.
     */
    protected function resolveHook(string|BackedEnum $hook): string
    {
        return $hook instanceof BackedEnum ? (string) $hook->value : $hook;
    }

    /**
     * Set filters from an array (used for caching).
     */
    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
        $this->isLoaded = true;
    }

    /**
     * Set wildcard filters from an array.
     */
    public function setWildcardFilters(array $filters): void
    {
        $this->wildcardFilters = $filters;
    }
}
