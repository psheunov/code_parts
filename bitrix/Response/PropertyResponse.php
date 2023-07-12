<?php


namespace Axxon\Response;


class PropertyResponse extends ApiResponse
{
    /** @var string $name */
    protected $name;
    /** @var array $values */
    protected $values;

    /**
     * CatalogResponse constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->name = $data['name'] ?? null;
        $this->values = $data['values'] ?? null;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getValues(): ?array
    {
        return $this->values;
    }

}