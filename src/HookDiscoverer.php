<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks;

use AlizHarb\LaravelHooks\Attributes\{HookAction, HookFilter};
use Illuminate\Support\Facades\Config;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class HookDiscoverer
{
    public function __construct(
        protected HookManager $manager
    ) {
    }

    /**
     * Discover hooks in the configured paths.
     *
     * @return void
     */
    public function discover(): void
    {
        $paths = Config::get('hooks.scan_paths', []);

        if (empty($paths)) {
            return;
        }

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            $this->scanDirectory($path);
        }
    }

    /**
     * Scan a directory for classes with hook attributes.
     *
     * @param string $path
     * @return void
     */
    protected function scanDirectory(string $path): void
    {
        $finder = new Finder();
        $finder->files()->in($path)->name('*.php');

        foreach ($finder as $file) {
            $class = $this->extractClassName($file);

            if ($class && (class_exists($class) || trait_exists($class))) {
                $this->discoverInClass($class);
            }
        }
    }

    /**
     * Discover hooks within a specific class.
     *
     * @param string $className
     * @return void
     */
    protected function discoverInClass(string $className): void
    {
        $reflection = new ReflectionClass($className);

        foreach ($reflection->getMethods() as $method) {
            // Only process public methods, or any methods if it's a trait (will be public in consumers anyway)
            if (! $method->isPublic() && ! trait_exists($className)) {
                continue;
            }

            // Actions
            foreach ($method->getAttributes(HookAction::class) as $attribute) {
                /** @var HookAction $instance */
                $instance = $attribute->newInstance();

                $this->manager->addAction(
                    $instance->hook,
                    [$className, $method->getName()],
                    $instance->priority,
                    $instance->acceptedArgs
                );
            }

            // Filters
            foreach ($method->getAttributes(HookFilter::class) as $attribute) {
                /** @var HookFilter $instance */
                $instance = $attribute->newInstance();

                $this->manager->addFilter(
                    $instance->hook,
                    [$className, $method->getName()],
                    $instance->priority,
                    $instance->acceptedArgs
                );
            }
        }
    }

    /**
     * Extract the fully qualified class name from a file.
     *
     * @param SplFileInfo $file
     * @return string|null
     */
    protected function extractClassName(SplFileInfo $file): ?string
    {
        $content = file_get_contents($file->getRealPath());

        if (! preg_match('/namespace\s+(.+?);/s', $content, $matches)) {
            return null;
        }

        $namespace = trim($matches[1]);
        $class = $file->getBasename('.php');

        return $namespace . '\\' . $class;
    }
}
