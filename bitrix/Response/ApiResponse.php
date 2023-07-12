<?php


namespace Axxon\Response;


abstract class ApiResponse
{
    const OK                              = 'OK';
    const INTERNAL_ERROR                  = 'INTERNAL_ERROR';
    const NOT_IMPLEMENTED                 = 'NOT_IMPLEMENTED';
    const USER_NOT_FOUND                  = 'USER_NOT_FOUND';
    const USER_ALREADY_IN_COMPANY         = 'USER_ALREADY_IN_COMPANY';
    const COMPANY_NOT_FOUND               = 'COMPANY_NOT_FOUND';
    const CREDENTIALS_NOT_FOUND           = 'CREDENTIALS_NOT_FOUND';
    const INVALID_PARAMETERS              = 'INVALID_PARAMETERS';
    const INVALID_INPUT_DATA              = 'INVALID_INPUT_DATA';
    const EMPTY_REQUEST_BODY              = 'EMPTY_REQUEST_BODY';
    const JOIN_REQUEST_ALREADY_EXISTS     = 'JOIN_REQUEST_ALREADY_EXISTS';
    const INVALID_REQUEST                 = 'INVALID_REQUEST';
    const ACCESS_DENIED                   = 'ACCESS_DENIED';
    const INDIVIDUAL_ALREADY_EXISTS       = 'INDIVIDUAL_ALREADY_EXISTS';
    const COUNTRY_NOT_FOUND               = 'COUNTRY_NOT_FOUND';
    const PROJECT_NOT_FOUND               = 'PROJECT_NOT_FOUND';
    const GOODS_ATTRIBUTE_VALUE_NOT_FOUND = 'GOODS_ATTRIBUTE_VALUE_NOT_FOUND';

    /** @var string $code */
    protected $code;

    /** @var string $message */
    protected $message;

    /** @var bool $success */
    protected $success;

    /**
     * ApiResponse constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->code    = $data['error_code'] ?? null;
        $this->message = $data['message'] ?? null;
        $this->success = $data['success'] ?? false;
    }

    /**
     * @return null|string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @return null|string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->success;
    }
}