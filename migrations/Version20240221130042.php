<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240221130042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question ADD case_scenario LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE quiz DROP title, DROP case_scenario, DROP is_approved');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE question DROP case_scenario');
        $this->addSql('ALTER TABLE quiz ADD title VARCHAR(255) NOT NULL, ADD case_scenario LONGTEXT NOT NULL, ADD is_approved TINYINT(1) NOT NULL');
    }
}
