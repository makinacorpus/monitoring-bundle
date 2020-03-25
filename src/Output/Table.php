<?php

declare(strict_types=1);

namespace MakinaCorpus\Monitoring\Output;

final class Table extends AbstractDisplayable
{
    /** @var ?list<string> */
    private $headers = null;

    /** @var list<list<string|Displayable */
    private $rows = [];

    /**
     * @param list<string> $headers
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    private function validateRow(array $row): array
    {
        return \array_map(
            \Closure::fromCallable([$this, 'validateRowValue']),
            $row
        );
    }

    /**
     * @param mixed $value
     * @return null|string|Displayable
     */
    private function validateRowValue($value)
    {
        if (null === $value || \is_string($value) || $value instanceof Displayable) {
            return $value;
        }
        if (\is_scalar($value) || (\is_object($value) && \method_exists($value, '__toString'))) {
            return (string)$value;
        }
        throw new \InvalidArgumentException(\sprintf("Value must be null, a string, or an instance of '%s'", Displayable::class));
    }

    /**
     * @param list<string|Displayable> $row
     */
    public function addRow(array $row): self
    {
        $this->rows[] = $this->validateRow($row);

        return $this;
    }

    /**
     * @return ?list<string>
     */
    public function getHeaders(): ?array
    {
        return $this->headers;
    }

    /**
     * @return list<list<string|Displayable>>
     */
    public function getRows(): array
    {
        return $this->rows;
    }
}
