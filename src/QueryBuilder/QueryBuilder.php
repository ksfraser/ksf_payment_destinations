<?php
namespace ksfraser\PaymentDestinations\QueryBuilder;

/**
 * Generic SQL Query Builder — candidate for ksf_modules_common or ksf_query_builder package.
 * @deprecated This module-specific copy should be replaced with a shared library version.
 * @see https://github.com/ksfraser/ksf_query_builder (proposed)
 */
class QueryBuilder
{
    protected string $table;
    protected array $where = [];
    protected array $fields = [];
    protected ?string $orderBy = null;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function select(array $fields = ['*']): self
    {
        $this->fields = $fields;
        return $this;
    }

    public function where(string $column, $value, string $op = '='): self
    {
        $this->where[] = ["$column $op", $value];
        return $this;
    }

    public function orderBy(string $column, string $dir = 'ASC'): self
    {
        $this->orderBy = "$column $dir";
        return $this;
    }

    public function toSql(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->fields) . ' FROM ' . $this->table;
        if (!empty($this->where)) {
            $clauses = array_map(fn($w) => $w[0], $this->where);
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }
        if ($this->orderBy) {
            $sql .= ' ORDER BY ' . $this->orderBy;
        }
        return $sql;
    }
}
