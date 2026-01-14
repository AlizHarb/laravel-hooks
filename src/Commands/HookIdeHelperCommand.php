<?php

declare(strict_types=1);

namespace AlizHarb\LaravelHooks\Commands;

use Illuminate\Console\Command;
use AlizHarb\LaravelHooks\HookManager;

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
     * @return int
     */
    public function handle(): int
    {
        $this->info('Generating IDE Helper metadata...');

        $content = <<<'PHP'
<?php

namespace PHPSTORM_META {
    override(\AlizHarb\LaravelHooks\Facades\Hook::applyFilters(0), map([
        '' => '@',
    ]));
    override(\AlizHarb\LaravelHooks\Facades\Hook::doAction(0), map([
        '' => '@',
    ]));
    override(\AlizHarb\LaravelHooks\HookManager::applyFilters(0), map([
        '' => '@',
    ]));
    override(\AlizHarb\LaravelHooks\HookManager::doAction(0), map([
        '' => '@',
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
