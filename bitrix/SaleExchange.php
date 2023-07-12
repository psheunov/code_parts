<?php


namespace Axxon;

use Axxon\Api\ProductsApi;
use Axxon\Api\PropertiesApi;
use Axxon\Api\SectionApi;
use Axxon\Api\WarehousesApi;
use Axxon\Entity\ExchangeJobTable;
use Axxon\Repository\ProductsRepository;
use Axxon\Repository\PropertiesRepository;
use Axxon\Repository\SectionsRepository;
use Axxon\Repository\WarehousesRepository;
use Axxon\Response\ApiResponse;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Exception;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SaleExchange
{

    /** @var ContainerInterface $container */
    private $container;

    /** @var Logger $logger */
    private $logger;

    /**
     * SaleExchange constructor.
     * @throws LoaderException
     */
    public function __construct(ContainerInterface $container, Logger $logger)
    {
        $this->container = $container;
        $this->logger    = $logger;

        $this->includeBitrixCore();
    }

    /**
     * @throws LoaderException
     */
    private function includeBitrixCore()
    {
        $homeDir = $this->container->getParameter('homeDir');
        if (file_exists($homeDir . '/bitrix/modules/main/include/prolog_before.php')) {
            define('NOT_CHECK_PERMISSIONS', true);
            /** @global string $DOCUMENT_ROOT */
            $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = $homeDir;

            require_once($homeDir . '/bitrix/modules/main/include/prolog_before.php');

            $GLOBALS['APPLICATION']->RestartBuffer();
            while (ob_get_level() > 0 && ob_end_clean()) {
            };

            ini_set('memory_limit', '4096M');

            Loader::includeModule('iblock');
            Loader::includeModule('sale');
        }
    }

    /**
     * @throws JobException
     */
    public function updateSections()
    {
        try {
            /** @var SectionApi $sectionApi */
            $sectionApi = $this->container->get(SectionApi::class);
            $response   = $sectionApi->all();

            if ($response->getCode() !== ApiResponse::OK) {
                throw new ApiException(ApiException::SERVER_ERROR, 500);
            }

            /** @var SectionsRepository $sectionsRepository */
            $sectionsRepository = $this->container->get(SectionsRepository::class);

            $sectionsRepository
                ->loadExternal($response->getSections())
                ->process();
        } catch (ApiException $e) {
            $this->logger->error($e->getMessage());
            throw new JobException(ExchangeJobTable::JOB_CATALOGS);
        }
    }

    /**
     * Deletes inactive sections
     */
    public function deleteInactiveSections()
    {
        /** @var SectionsRepository $sectionsRepository */
        $sectionsRepository = $this->container->get(SectionsRepository::class);
        $sectionsRepository->deleteInactive();
    }

    /**
     * @throws JobException
     */
    public function updateProperties()
    {
        try {
            /** @var PropertiesApi $propertiesApi */
            $propertiesApi      = $this->container->get(PropertiesApi::class);
            $propertiesResponse = $propertiesApi->all();

            if ($propertiesResponse->getCode() !== ApiResponse::OK) {
                throw new ApiException(ApiException::SERVER_ERROR, 500);
            }

            /** @var PropertiesRepository $propertiesRepository */
            $propertiesRepository = $this->container->get(PropertiesRepository::class);
            $propertiesRepository
                ->loadExternal($propertiesResponse->getProperties())
                ->process();

        } catch (ApiException $e) {
            $this->logger->info($e->getMessage());
            throw new JobException(ExchangeJobTable::JOB_ATTRIBUTES);
        }
    }

    /**
     * @param int $offset
     * @param int $total
     * @param int $timestamp
     */
    public function updateProducts(int $offset, int $total, int $timestamp = 0)
    {
        try {
            /** @var ProductsApi $productsApi */
            $productsApi = $this->container->get(ProductsApi::class);

            /** @var ProductsRepository $productsRepository */
            $productsRepository = $this->container->get(ProductsRepository::class);

            $start    = $offset;
            $products = [];

            $limit           = $this->container->getParameter('products.limit');
            $processingChunk = $this->container->getParameter('products.processing.chunk');

            do {
                if (count($products) >= $processingChunk) {
                    $productsRepository->updateProducts($products);
                    $products = [];
                }

                if ($timestamp) {
                    $response = $productsApi->updatedSince($timestamp, $start, $limit);
                } else {
                    $response = $productsApi->all($start, $limit);
                }

                $products = array_merge($products, $response->getProducts());

                $start += $limit;
                $total -= $limit;
            } while (count($response->getProducts()) >= $limit && $total > 0);

            if ($products) {
                $productsRepository->updateProducts($products);
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @param int $timestamp
     * @return void
     * @throws JobException
     */
    public function deactivateProductsUnavailableSince(int $timestamp)
    {
        try {
            /** @var ProductsApi $productsApi */
            $productsApi = $this->container->get(ProductsApi::class);

            /** @var ProductsRepository $productsRepository */
            $productsRepository = $this->container->get(ProductsRepository::class);

            $start           = 0;
            $products        = [];
            $limit           = $this->container->getParameter('products.limit');
            $processingChunk = $this->container->getParameter('products.processing.chunk');

            do {
                if (count($products) > $processingChunk) {
                    $productsRepository->deactivateProducts($products);
                    $products = [];
                }

                $response = $productsApi->unavailableSince($timestamp, $start, $limit);
                $products = array_merge($products, $response->getProducts());

                $start += $limit;
            } while (count($response->getProducts()) == $limit);

            if ($products) {
                $productsRepository->deactivateProducts($products);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new JobException(ExchangeJobTable::JOB_PRODUCTS);
        }
    }


    /**
     * @throws JobException
     */
    public function updateWarehouses()
    {
        try {
            /** @var WarehousesApi $warehousesApi */
            $warehousesApi = $this->container->get(WarehousesApi::class);
            $response      = $warehousesApi->all();

            if ($response->getCode() !== ApiResponse::OK) {
                throw new ApiException(ApiException::SERVER_ERROR, 500);
            }

            /** @var WarehousesRepository $warehousesRepository */
            $warehousesRepository = $this->container->get(WarehousesRepository::class);
            $warehousesRepository
                ->loadExternal($response->getWarehouses())
                ->process();

        } catch (ApiException $e) {
            $this->logger->error($e->getMessage());
            throw new JobException(ExchangeJobTable::JOB_WAREHOUSES);
        }
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
}