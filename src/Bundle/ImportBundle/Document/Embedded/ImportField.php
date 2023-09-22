<?php

namespace Integrated\Bundle\ImportBundle\Document\Embedded;

/**
 * Embedded document ImportField.
 */
class ImportField
{
    /**
     * @var int
     */
    protected $column;

    /**
     * @var string
     */
    protected $sourceField;

    /**
     * @var string
     */
    protected $mappedField;

    /**
     * @var array
     */
    protected $options = [];

    public function getOptions()
    {
        return $this->options;
    }

    public function getColumn(): int
    {
        return $this->column;
    }

    public function setColumn(int $column): void
    {
        $this->column = $column;
    }

    public function getSourceField(): ?string
    {
        return $this->sourceField;
    }

    public function setSourceField(string $sourceField): void
    {
        $this->sourceField = $sourceField;
    }

    public function getMappedField(): ?string
    {
        return $this->mappedField;
    }

    public function setMappedField(string $mappedField): void
    {
        $this->mappedField = $mappedField;
    }

    /**
     * Set the options of the field.
     *
     * @param array $options The options of the form field
     *
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;

        return $this;
    }
}
