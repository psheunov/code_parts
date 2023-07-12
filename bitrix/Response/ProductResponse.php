<?php


namespace Axxon\Response;


class ProductResponse extends ApiResponse
{
    /**
     * @var int
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $price;
    /**
     * @var array
     */
    private $count;
    /**
     * @var array
     */
    private $sections;
    /**
     * @var array
     */
    private $dimensions;
    /**
     * @var array
     */
    private $properties;

    /**
     * CatalogResponse constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $single = $data['products'][0] ?? null;

        if ($single) {
            $this->id = $single['id'] ?? null;
            $this->name = $single['name'] ?? null;
            $this->price = $single['price'] ?? null;
            $this->count = $single['count'] ?? null;
            $this->sections = $single['sections'] ?? null;
            $this->dimensions = $single['dimensions'] ?? null;
            $this->properties = $single['properties'] ?? null;
        }
    }

    /**
     * @return array
     */
    public function getProperties(): ?array
    {
        return $this->properties;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getPrice(): ?int
    {
        return $this->price;
    }

    /**
     * @return array
     */
    public function getCount(): ?array
    {
        return $this->count;
    }

    /**
     * @return array
     */
    public function getSections(): ?array
    {
        return $this->sections;
    }

    /**
     * @return array
     */
    public function getDimensions(): ?array
    {
        return $this->dimensions;
    }
}