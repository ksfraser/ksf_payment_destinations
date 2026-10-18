<?php
declare(strict_types=1);
namespace Tests\Support;

use ksfraser\CommonDb\Contract\DbConnectionInterface;

/**
 * In-memory DbConnectionInterface test double.
 *
 * PaymentDestinationRepository takes its DbConnectionInterface by constructor
 * injection (see its third constructor parameter), so it is fully testable
 * without an FA runtime, a live MySQL server or the famock db_* stubs. The
 * previous versions of these tests were skipped with the reason "Requires FA
 * db_* functions - run in integration test context"; that reason was wrong,
 * the class was already built for injection.
 *
 * This double is deliberately not a SQL engine. It models exactly the storage
 * semantics PaymentDestinationRepository depends on, and records every
 * statement so tests can assert on the SQL as well as on the return value:
 *
 *   - the destination table is a row set keyed by payment_term;
 *   - payment_term is the PRIMARY KEY, so a duplicate INSERT affects 0 rows,
 *     which is exactly how MySQL rejects it;
 *   - DDL (CREATE TABLE / SHOW TABLES) is answered from a table registry.
 *
 * @BABOK Related: UT-PD-001-002-002-select-row.md
 */
final class InMemoryDbConnection implements DbConnectionInterface
{
    /** @var array<int, array<string, mixed>> rows keyed by payment_term */
    private $rows = [];

    /** @var array<int, string> every statement executed, in order */
    public $executed = [];

    /** @var array<int, array{0: string, 1: array}> every query, with params */
    public $queried = [];

    /** @var array<int, string> known table names, for CREATE/SHOW TABLES */
    private $tables = [];

    /** @var int */
    private $lastInsertId = 0;

    public function __construct(array $rows = [])
    {
        foreach ($rows as $row) {
            $this->rows[(int) $row['payment_term']] = $row;
        }
    }

    public function fetchAssoc(string $sql, array $params = []): ?array
    {
        $this->queried[] = [$sql, $params];
        $term = $this->paymentTermFrom($sql, $params);
        if ($term === null) {
            return null;
        }
        return isset($this->rows[$term]) ? $this->rows[$term] : null;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $this->queried[] = [$sql, $params];
        // ORDER BY terms ASC is what findAll() asks for; honour it so an
        // ordering bug in the repository would show up here rather than
        // passing by accident.
        $rows = array_values($this->rows);
        usort($rows, static function (array $a, array $b): int {
            return (int) $a['payment_term_name'] <=> (int) $b['payment_term_name'];
        });
        return $rows;
    }

    public function fetchScalar(string $sql, array $params = [], $default = null)
    {
        $this->queried[] = [$sql, $params];
        if (stripos($sql, 'SHOW TABLES') === 0) {
            $wanted = $this->param($params, 'tableName');
            $wanted = $wanted === null ? '' : (string) $wanted;
            return in_array($wanted, $this->tables, true) ? $wanted : $default;
        }
        return $default;
    }

    public function executeUpdate(string $sql, array $params = []): int
    {
        $this->executed[] = $sql;

        if (stripos($sql, 'CREATE TABLE') === 0 || stripos($sql, 'SHOW TABLES') === 0) {
            // CREATE TABLE IF NOT EXISTS <name>: register the table.
            if (preg_match('/CREATE TABLE(?:\s+IF NOT EXISTS)?\s+(\S+)/i', $sql, $m)) {
                $name = trim($m[1], '`');
                if (!in_array($name, $this->tables, true)) {
                    $this->tables[] = $name;
                }
            }
            // DDL reports 0 affected rows, which is what MySQL returns and what
            // createTable() relies on when it tests `>= 0`.
            return 0;
        }

        if (stripos($sql, 'INSERT') === 0) {
            $term = (int) $this->param($params, 'paymentTerm');
            if (isset($this->rows[$term])) {
                // PRIMARY KEY (payment_term) collision: MySQL rejects the row
                // and reports 0 affected rows. insert() returns `> 0`, so this
                // surfaces as a false return rather than a silent overwrite.
                return 0;
            }
            $this->rows[$term] = [
                'payment_term' => $term,
                'payment_term_name' => '',
                'bank_account' => (int) $this->param($params, 'bankAccount'),
                'bank_account_name' => '',
            ];
            $this->lastInsertId = $term;
            return 1;
        }

        if (stripos($sql, 'UPDATE') === 0) {
            $term = (int) $this->param($params, 'paymentTerm');
            if (!isset($this->rows[$term])) {
                return 0;
            }
            $this->rows[$term]['bank_account'] = (int) $this->param($params, 'bankAccount');
            return 1;
        }

        if (stripos($sql, 'DELETE') === 0) {
            $term = $this->paymentTermFrom($sql, $params);
            if ($term !== null && isset($this->rows[$term])) {
                unset($this->rows[$term]);
                return 1;
            }
            return 0;
        }

        return 0;
    }

    public function lastInsertId(): int
    {
        return $this->lastInsertId;
    }

    public function quote($value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        return "'" . addslashes((string) $value) . "'";
    }

    public function beginTransaction(): void
    {
    }

    public function commit(): void
    {
    }

    public function rollBack(): void
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function rows(): array
    {
        return $this->rows;
    }

    public function lastStatement(): string
    {
        return $this->executed ? (string) end($this->executed) : '';
    }

    /**
     * Extract the payment_term a statement targets, from either a `?`
     * positional param (QueryBuilder) or a `:paymentTerm` named one (the
     * hand-written INSERT/UPDATE SQL in the repository).
     */
    private function paymentTermFrom(string $sql, array $params)
    {
        $value = $this->param($params, 'paymentTerm');
        if ($value === null) {
            return null;
        }
        return (int) $value;
    }

    /**
     * @param  array $params
     * @param  string $name
     * @return mixed|null
     */
    private function param(array $params, string $name)
    {
        if (array_key_exists($name, $params)) {
            return $params[$name];
        }
        // QueryBuilder emits positional `?` params; the first one is
        // payment_term for every statement the repository builds here.
        return isset($params[0]) ? $params[0] : null;
    }
}