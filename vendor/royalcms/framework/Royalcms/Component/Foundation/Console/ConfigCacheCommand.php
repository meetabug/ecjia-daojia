<?php

namespace Royalcms\Component\Foundation\Console;

use Royalcms\Component\Console\Command;
use Royalcms\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;

class ConfigCacheCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'config:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a cache file for faster configuration loading';

    /**
     * The filesystem instance.
     *
     * @var \Royalcms\Component\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new config cache command instance.
     *
     * @param  \Royalcms\Component\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->call('config:clear');

        $config = $this->getFreshConfiguration();

        $this->excludeSpecialConfiguration($config);

        $this->files->put(
            $this->royalcms->getCachedConfigPath(), '<?php return '.var_export($config, true).';'.PHP_EOL
        );

        $this->info('Configuration cached successfully!');
    }

    /**
     * Boot a fresh copy of the application configuration.
     *
     * @return array
     */
    protected function getFreshConfiguration()
    {
        $royalcms = require $this->royalcms->bootstrapPath().'/royalcms.php';

        $royalcms->make('Royalcms\Component\Contracts\Console\Kernel')->bootstrap();

        return $royalcms['config']->all();
    }

    /**
     * Exclude special configuration files
     */
    protected function excludeSpecialConfiguration(& $config)
    {
        if ($this->option('exclude'))
        {
            unset($config['*::system']);
            unset($config['*::namespaces']);
            unset($config['*::provider']);
            unset($config['*::cache']);
            unset($config['*::app']);
            unset($config['*::smarty']);
            unset($config['*::session']);
            unset($config['*::view']);
            unset($config['excel::export']);
        }

    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['exclude', null, InputOption::VALUE_NONE, 'Exclude special configuration files to be compiled.'],
        ];
    }

}
