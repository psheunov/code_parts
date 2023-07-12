<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use MeiliSearch\Client;

class  SearchObserver
{
    /**
     * @var array
     */
    private array $locales;

    public function __construct()
    {
        $this->locales = array_keys(config('multilingual.locales'));
    }

    /**
     * @param Model $model
     * @return string
     */
    private function getUid(Model $model): string
    {
        $className = strtolower(class_basename($model->getMorphClass()));

        return $className . '_' . $model->id;
    }

    /**
     * @param string $locale
     * @return string
     */
    private function getSearchIndex(string $locale): string
    {
        return sprintf('Search%s', ucfirst($locale));
    }

    /**
     * @param Model $model
     * @param string $locale
     */
    private function deleteFromIndex(Model $model, string $locale)
    {
        $client = resolve(Client::class);
        $client->index($this->getSearchIndex($locale))->deleteDocument($this->getUid($model));
    }

    /**
     * @param Model $model
     */
    private function saveModel(Model $model, $locale)
    {
        $active = $model->active ?? false;

        if (!$active) {
            $this->deleteFromIndex($model, $locale);
        } else {
            /** @var Client $client */
            $client = resolve(Client::class);
            $client->index($this->getSearchIndex($locale))->addDocuments([$model->toSearchableArray($locale)]);
        }
    }

    private function saveIndexForLocales(Model $model)
    {
        foreach ($this->locales as $locale) {
            $this->saveModel($model, $locale);
        }
    }

    /**
     * При создании экземпляра модели создает его документ в индексе MeiliSearch
     *
     * @param Model $model - экземпляр модели, который был создан
     */
    public function created(Model $model)
    {
        $this->saveIndexForLocales($model);
    }

    /**
     * При обновлении экземпляра модели обновляет его документ в индексе MeiliSearch
     *
     * @param Model $model - экземпляр модели, который был обновлен
     */
    public function updated(Model $model)
    {
        $this->saveIndexForLocales($model);
    }

    /**
     * При удалении экземпляра модели удаляет его документ в индексе MeiliSearch
     *
     * @param Model $model - экземпляр модели, который был создан
     */
    public function deleted(Model $model)
    {
        foreach ($this->locales as $locale) {
            $this->deleteFromIndex($model, $locale);
        }
    }
}
