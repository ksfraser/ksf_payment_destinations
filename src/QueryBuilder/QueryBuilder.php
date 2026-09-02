<?php
namespace ksfraser\PaymentDestinations\QueryBuilder;

class QueryBuilder
{
    protected string $table;
    protected array $fields = ['*'];
    protected array $where = [];
    protected ?string $orderBy = null;
    protected array $joins = [];
    protected ?string $delete = null;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function select(array $fields = ['*']): self
    {
        $this->fields = $fields;
        $this->delete = null;
        return $this;
    }

    public function delete(): self
    {
        $this->delete = $this->table;
        $this->fields = [];
        return $this;
    }

    public function join(string $table, string $on, string $type = 'LEFT'): self
    {
        $this->joins[] = "$type JOIN $table ON $on";
        return $this;
    }

    /**
     * Add a WHERE condition. In non-FA context, use parameterized form.
     * In FA context (where db_query doesn't support prepared statements),
     * pass $paramValue as null to embed the value directly in the clause.
     */
    public function where(string $column, $value, string $op = '='): self
    {
        if ($value === null) {
            $this->where[] = ["$column $op", null];
        } else {
            $this->where[] = ["$column $op ?", $value];
        }
        return $this;
    }

    public function orderBy(string $column, string $dir = 'ASC'): self
    {
        $this->orderBy = "$column $dir";
        return $this;
    }

    public function toSql(): string
    {
        if ($this->delete !== null) {
            $sql = 'DELETE FROM ' . $this->delete;
        } else {
            $sql = 'SELECT ' . implode(', ', $this->fields) . ' FROM ' . $this->table;
            foreach ($this->joins as $join) {
                $sql .= ' ' . $join;
            }
        }
        if (!empty($this->where)) {
            $clauses = array_map(fn($w) => $w[0], $this->where);
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }
        if ($this->orderBy) {
            $sql .= ' ORDER BY ' . $this->orderBy;
        }
        return $sql;
    }

    /**
     * Substitute ? placeholders with escaped values.
     * Safe for integers and strings. FA uses this since db_query has no prepared statements.
     */
    public function toParams(): array
    {
        return array_map(fn($w) => $w[1], $this->where);
    }

    /**
     * Return SQL with ? placeholders substituted (for FA compatibility).
     * Uses intval/string escaping for safety.
     */
    public function toFaSql(): string
    {
        $sql = $this->toSql();
        foreach ($this->toParams() as $param) {
            if ($param === null) {
                continue;
            }
            $replacement = is_int($param) ? (string) $param : "'" . addslashes((string) $param) . "'";
            $sql = preg_replace('/\?/', $replacement, $sql, 1);
        }
        return $sql;
    }
}
