<?php


namespace Axxon\Response;


class PropertiesResponse extends ApiResponse
{
    /** @var array $properties */
    protected $properties;

    /**
     * CatalogResponse constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->properties = $data['properties'] ?? null;
    }

    /**
     * @return array
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }
}