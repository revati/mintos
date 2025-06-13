<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250613192351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE "rates" (id UUID NOT NULL, "from_currency" VARCHAR(3) NOT NULL, "to_currency" VARCHAR(3) NOT NULL, rate NUMERIC(10, 6) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction_entries ADD description VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction_entries ALTER type TYPE VARCHAR(255)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transactions ADD amount BIGINT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transactions ADD currency VARCHAR(3) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE "rates"
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "transaction_entries" DROP description
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "transaction_entries" ALTER type TYPE VARCHAR(6)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "transactions" DROP amount
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "transactions" DROP currency
        SQL);
    }
}
