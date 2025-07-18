<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250718070540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice CHANGE appointment_id appointment_id INT DEFAULT NULL, CHANGE ammount ammount NUMERIC(10, 2) DEFAULT NULL, CHANGE status status VARCHAR(255) DEFAULT NULL, CHANGE issued_at issued_at DATETIME DEFAULT NULL, CHANGE paid_at paid_at DATETIME DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE invoice CHANGE appointment_id appointment_id INT NOT NULL, CHANGE ammount ammount NUMERIC(10, 2) NOT NULL, CHANGE status status VARCHAR(255) NOT NULL, CHANGE issued_at issued_at DATETIME NOT NULL, CHANGE paid_at paid_at DATETIME NOT NULL
        SQL);
    }
}
