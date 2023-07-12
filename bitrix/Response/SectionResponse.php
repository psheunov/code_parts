<?php


namespace Axxon\Response;


class SectionResponse extends ApiResponse
{
    /** @var array $sections */
    protected $sections;

    /**
     * CatalogResponse constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->sections = $data['sections'] ?? null;
    }

    /**
     * @return array
     */
    public function getSections(): ?array
    {
        return $this->sections;
    }
}