<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\News;
use App\Models\Page;
use App\Models\CaseStudy;
use App\Models\Partner;
use App\Models\SolutionPartner;
use Exception;
use Illuminate\Console\Command;
use MeiliSearch\Client;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;

class ReindexSearch extends Command
{
    const MODEL_NAME = 'Search%s';

    const MODELS = [
        News::class,
        Page::class,
        Event::class,
        Partner::class,
        SolutionPartner::class
    ];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'axxon:reindex-search {locale=null}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex search models for locales';

    private $deleteCommand;
    private $indexCommand;
    private $importCommand;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param Client $searchClient
     * @param $locale
     * @return int
     */
    public function handle(Client $searchClient): int
    {
        $this->initCommands();
        $locales = array_keys(config('multilingual.locales'));

        if (in_array($this->argument('locale'), $locales)) {
            $this->reindexLocale($this->argument('locale'), $searchClient);
        } else {
            foreach ($locales as $locale) {
                $this->reindexLocale($locale, $searchClient);
            }
        }

        try {
            $indexName = (new CaseStudy())->searchableAs();
            $this->deleteCommand->run(new ArrayInput(['name' => $indexName]), $this->getOutput());

            $index = $searchClient->index($indexName);

            $index->updateSearchableAttributes(['slug']);
            $index->updateFilterableAttributes(['active', 'tags']);
            $index->updateSortableAttributes(['order', 'date']);

            $this->importCommand->run(new ArrayInput(['model' => CaseStudy::class]), $this->getOutput());
        } catch (Exception | ExceptionInterface $e) {
        }

        return 0;
    }

    /**
     * init index commands
     */
    private function initCommands()
    {
        $app = $this->getApplication();
        $this->deleteCommand = $app->find('scout:delete-index');
        $this->indexCommand = $app->find('scout:index');
        $this->importCommand = $app->find('scout:import');
    }

    /**
     * @param string $class
     * @param string $locale
     * @return void
     */
    private function importDocuments(string $class, string $locale)
    {
        $indexName = sprintf('Search%s', ucfirst($locale));

        /** @var Client $client */
        $client = resolve(Client::class);

        $documents = $class::where('active', '=', true)->get();

        $documents = $documents->map(
            function ($document) use ($locale) {
                return $document->toSearchableArray($locale);
            }
        );

        $client->index($indexName)->addDocuments($documents->toArray());
    }

    /**
     * @param $locale
     * @param Client $searchClient
     */
    private function reindexLocale($locale, Client $searchClient)
    {
        $indexName = sprintf('Search%s', ucfirst($locale));
        try {
            $this->deleteCommand->run(new ArrayInput(['name' => $indexName]), $this->getOutput());
            $this->indexCommand->run(new ArrayInput(['name' => $indexName]), $this->getOutput());

            $index = $searchClient->index($indexName);
            $index->updateSearchableAttributes(['title', 'content']);
            $index->updateFilterableAttributes(['locales', 'model_name']);
            $index->updateRankingRules(
                [
                    'sort',
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'exactness'
                ]
            );
            $index->updateSortableAttributes(['title', 'updated_at']);

            foreach (self::MODELS as $model) {
                $this->importDocuments($model, $locale);
            }
        } catch (Exception | ExceptionInterface $e) {
        }
    }
}
