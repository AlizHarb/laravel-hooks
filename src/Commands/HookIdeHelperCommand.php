<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Commands;

use AlizHarb\LaravelHooks\HookManager;
use Illuminate\Console\Command;

/**
 * Generates PHPStorm meta file for Hook Facade autocompletion.
 */
class HookIdeHelperCommand extends Command
{
    protected $signature = 'hook:ide-helper';
    protected $description = 'Generate PHPStorm metadata for hooks';

    /**
     * Execute the console command.
     *
     * @param HookManager $manager
     * @return int
     */
    public function handle(HookManager $manager): int
    {
        $this->info('Generating IDE Helper metadata...');

        $hooks = $manager->getRegisteredHookNames();
        $map = [];

        foreach ($hooks as $hook) {
            $map[] = "        '{$hook}' => '@',";
        }

        $mapString = implode("\n", $map);

        $content = <<<PHP
<?php

namespace PHPSTORM_META {
    override(\AlizHarb\LaravelHooks\Facades\Hook::applyFilters(0), map([
{$mapString}
    ]));
    override(\AlizHarb\LaravelHooks\Facades\Hook::doAction(0), map([
{$mapString}
    ]));
    override(\AlizHarb\LaravelHooks\HookManager::applyFilters(0), map([
{$mapString}
    ]));
    override(\AlizHarb\LaravelHooks\HookManager::doAction(0), map([
{$mapString}
    ]));
}
PHP;

        if (! is_dir(base_path('.phpstorm.meta.php'))) {
            mkdir(base_path('.phpstorm.meta.php'), 0755, true);
        }

        file_put_contents(base_path('.phpstorm.meta.php/hooks.meta.php'), $content);

        $this->info('Metadata generated!');

        return self::SUCCESS;
    }
}
