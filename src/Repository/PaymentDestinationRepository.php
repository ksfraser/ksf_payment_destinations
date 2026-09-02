<?php
namespace ksfraser\PaymentDestinations\Repository;

use ksfraser\CommonDb\Contract\DbConnectionInterface;
use ksfraser\PaymentDestinations\QueryBuilder\QueryBuilder;

class PaymentDestinationRepository implements PaymentDestinationRepositoryInterface
{
    protected string $tableName;
    protected string $tbPref;
    protected DbConnectionInterface $db;

    public function __construct(string $tableName, string $tbPref = '0_', ?DbConnectionInterface $db = null)
    {
        $this->tableName = $tableName;
        $this->tbPref = $tbPref;
        $this->db = $db ?? new \ksfraser\CommonDb\Adapter\FaDbAdapter($tbPref);
    }

    public function findByPaymentTerm(int $term): ?array
    {
        $qb = new QueryBuilder($this->tableName);
        $qb->select()->where('payment_term', $term);
        return $this->db->fetchAssoc($qb->toSql(), ['payment_term' => $term]);
    }

    public function findAll(): array
    {
        $qb = new QueryBuilder("{$this->tableName} pd");
        $qb->select([
            'pd.payment_term',
            'pt.terms as payment_term_name',
            'ba.bank_account_name',
            'ba.account_code as bank_account_code'
        ]);
        $qb->join("{$this->tbPref}payment_terms pt", 'pt.terms_indicator = pd.payment_term');
        $qb->join("{$this->tbPref}bank_accounts ba", 'ba.id = pd.bank_account');
        $qb->orderBy('pt.terms', 'ASC');
        return $this->db->fetchAll($qb->toSql());
    }

    public function insert(int $paymentTerm, int $bankAccount): bool
    {
        $sql = "INSERT INTO {$this->tableName} (payment_term, payment_term_name, bank_account, bank_account_name)
                SELECT :paymentTerm, pt.terms, :bankAccount, ba.bank_account_name
                FROM {$this->tbPref}payment_terms pt, {$this->tbPref}bank_accounts ba
                WHERE pt.terms_indicator = :paymentTerm AND ba.id = :bankAccount";
        return $this->db->executeUpdate($sql, ['paymentTerm' => $paymentTerm, 'bankAccount' => $bankAccount]) > 0;
    }

    public function update(int $paymentTerm, int $bankAccount): bool
    {
        $sql = "UPDATE {$this->tableName}
                SET bank_account = :bankAccount,
                    bank_account_name = (SELECT bank_account_name FROM {$this->tbPref}bank_accounts WHERE id = :bankAccount)
                WHERE payment_term = :paymentTerm";
        return $this->db->executeUpdate($sql, ['paymentTerm' => $paymentTerm, 'bankAccount' => $bankAccount]) > 0;
    }

    public function upsert(int $paymentTerm, int $bankAccount): bool
    {
        $existing = $this->findByPaymentTerm($paymentTerm);
        if ($existing) {
            return $this->update($paymentTerm, $bankAccount);
        }
        return $this->insert($paymentTerm, $bankAccount);
    }

    public function deleteByTerm(int $term): bool
    {
        $qb = new QueryBuilder($this->tableName);
        $qb->delete()->where('payment_term', $term);
        return $this->db->executeUpdate($qb->toSql(), ['payment_term' => $term]) >= 0;
    }

    public function createTable(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS {$this->tableName} (
            payment_term INT(11) NOT NULL DEFAULT 0,
            payment_term_name VARCHAR(100) NOT NULL DEFAULT '',
            bank_account INT(11) NOT NULL DEFAULT 0,
            bank_account_name VARCHAR(100) NOT NULL DEFAULT '',
            PRIMARY KEY (payment_term)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        return $this->db->executeUpdate($sql) >= 0;
    }

    public function tableExists(): bool
    {
        $sql = "SHOW TABLES LIKE :tableName";
        $result = $this->db->fetchScalar($sql, ['tableName' => $this->tableName]);
        return $result !== null;
    }
}
