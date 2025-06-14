<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250614112614 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction_entries ADD counterparty_id UUID NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE transaction_entries ADD CONSTRAINT FK_5B528F27DB1FAD05 FOREIGN KEY (counterparty_id) REFERENCES "accounts" (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5B528F27DB1FAD05 ON transaction_entries (counterparty_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE "transaction_entries" DROP CONSTRAINT FK_5B528F27DB1FAD05
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5B528F27DB1FAD05
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "transaction_entries" DROP counterparty_id
        SQL);
    }
}
