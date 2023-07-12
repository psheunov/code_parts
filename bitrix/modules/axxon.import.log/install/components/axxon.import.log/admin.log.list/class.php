<?php

namespace Axxon\Import\Log\Components;

use Axxon\Import\Log\ImportLogger;
use Bitrix\Main\Component\ParameterSigner;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Loader;
use CBitrixComponent;
use Exception;
use MongoDB\Model\BSONDocument;

class AdminLogList extends CBitrixComponent implements Controllerable
{
    const MODULE_ID = 'axxon.import.log';

    /**
     * AdminLogList constructor.
     *
     * @param null $component
     * @throws Exception
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
        Loader::includeModule(self::MODULE_ID);
    }

    /**
     * @throws Exception
     */
    public function executeComponent()
    {
        $importLogger            = new ImportLogger((int)$this->arParams['ELEMENT_ID']);
        $this->arResult['PAGES'] = $importLogger->pages;

        $rows = [];
        $i    = 0;

        /** @var BSONDocument $row */
        foreach ($importLogger->getProductLog() as $row) {
            $rows[$i] = $row->getArrayCopy();
            unset($rows[$i++]['_id']);
        }

        $this->arResult['ROWS'] = $rows;

        $this->includeComponentTemplate();
    }

    /**
     * Получаем параметры используя подписи
     *
     * @param string $signedParameters
     * @return array
     */
    private function getParams(string $signedParameters): array
    {
        try {
            $signer = new ParameterSigner();
            return $signer->unsignParameters($this->__name, $signedParameters);
        } catch (Exception $e) {
        }

        return [];
    }

    /**
     * Метод получения элементов по ajax
     *
     * @param string $signedParameters
     * @param int $page
     * @return array
     */
    public function listAction(string $signedParameters, int $page): array
    {
        $response = [];
        try {
            $params       = $this->getParams($signedParameters);
            $importLogger = new ImportLogger((int)$params['ELEMENT_ID']);

            $response = [
                'rows' => $importLogger->getProductLog($page)
            ];
        } catch (Exception $exception) {
        }

        return $response;
    }

    /**
     * Определение экшнов для ajax запросов
     * @return array
     */
    public function configureActions(): array
    {
        return [
            'list' => [
                'prefilters' => [
                    new HttpMethod([HttpMethod::METHOD_POST]),
                ],
            ],
        ];
    }

    /**
     * Список нужных параметров компонента
     *
     * @return array
     */
    protected function listKeysSignedParameters(): array
    {
        return [
            'ELEMENT_ID'
        ];
    }
}