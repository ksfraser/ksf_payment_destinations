-- ksf_payment_destinations module schema
-- Maps payment terms to bank accounts for direct invoice routing

CREATE TABLE IF NOT EXISTS `0_ksf_payment_destinations` (
    `payment_term` INT(11) NOT NULL DEFAULT 0,
    `payment_term_name` VARCHAR(100) NOT NULL DEFAULT '',
    `bank_account` INT(11) NOT NULL DEFAULT 0,
    `bank_account_name` VARCHAR(100) NOT NULL DEFAULT '',
    PRIMARY KEY (`payment_term`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
