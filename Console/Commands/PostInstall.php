<?php

namespace App\Console\Commands;

use App\Service\Bitrix24;
use App\Service\Translations as TranslationsService;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class PostInstall extends Command
{
    const TTL = 60 * 24;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'axxon:post-install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post install actions';

    /**
     * @var false|string
     */
    private $basePath;

    /**
     * @var string
     */
    private $homeDir;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->basePath = base_path();
        $this->homeDir  = dirname($this->basePath);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $commands = [
            sprintf("ln -snf %s %s/%s", $this->basePath, $this->homeDir, env('APP_DOMAIN')),
            sprintf("ln -snf %s/static %s", $this->homeDir, storage_path('app/public')),
        ];

        try {
            $this->info('Start composer post install script');

            foreach ($commands as $command) {
                $process = Process::fromShellCommandline($command, $this->homeDir);
                $process->run();

                if (!$process->isSuccessful()) {
                    $this->error($process->getCommandLine() . "\t<error>Error</error>");
                    $this->error("Error output: " . $process->getErrorOutput());
                }

                $this->line($process->getCommandLine() . "\t<info>Success</info>");
            }

            cache()->store('redis')->forget('translations');
            TranslationsService::getAll();
            Bitrix24::getCountries();

            foreach (config('multilingual.locales') as $locale => $name) {
                $locale = strtoupper($locale);

                cache()->store('redis')->forget('REASON.LIST.CRM.' . $locale);
                cache()->store('redis')->forget('ADVICE.SOURCES.CRM.' . $locale);

                Bitrix24::getReasons($locale);
                Bitrix24::getAdviceSources($locale);
            }

            cache()->store('redis')->remember('horizon_restart', self::TTL, function () {
                return 'horizon:terminate';
            });
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->info('Finished composer post install script');
        return 0;
    }
}
