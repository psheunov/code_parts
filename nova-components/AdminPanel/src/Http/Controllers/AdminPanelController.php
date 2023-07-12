<?php

namespace Axxon\AdminPanel\Http\Controllers;

use App\Console\Commands\PostInstall;
use App\Service\Bitrix24;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class AdminPanelController
{
    /**
     * @return JsonResponse
     * @throws \Exception
     */
    public function restartHorizon(): JsonResponse
    {
        cache()->store('redis')->remember(
            'horizon_restart',
            PostInstall::TTL,
            fn() => 'horizon:terminate'
        );

        return response()->json(true);
    }

    /**
     * @return JsonResponse
     */
    public function researchSite(): JsonResponse
    {
        dispatch(fn() => Artisan::call('axxon:reindex-search'));

        return response()->json(true);
    }

    /**
     * @return JsonResponse
     */
    public function researchSiteLocale(string $locale): JsonResponse
    {
        dispatch(fn() => Artisan::call(sprintf('axxon:reindex-search %s', $locale)));

        return response()->json(true);
    }

    /**
     * @return JsonResponse
     */
    public function researchBlog(): JsonResponse
    {
        dispatch(fn() => Artisan::call('blog:search-configure'));

        return response()->json(true);
    }

    /**
     * @return JsonResponse
     */
    public function cacheClear(): JsonResponse
    {
        dispatch(fn() => Artisan::call('optimize:clear'));

        return response()->json(true);
    }

    /**
     * @return JsonResponse
     */
    public function redisClear(): JsonResponse
    {

        try {
            cache()->store('redis')->forget('translations');
            foreach (config('multilingual.locales') as $locale => $name) {
                $locale = strtoupper($locale);

                cache()->store('redis')->forget('REASON.LIST.CRM.' . $locale);
                cache()->store('redis')->forget('ADVICE.SOURCES.CRM.' . $locale);

                Bitrix24::getReasons($locale);
                Bitrix24::getAdviceSources($locale);
            }
        } catch (Exception $e) {
            return response()->json(false);
        }

        return response()->json(true);
    }
}
