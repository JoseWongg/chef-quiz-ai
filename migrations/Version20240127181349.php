<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240127181349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE assigned_quiz (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, assigner_id INT NOT NULL, chef_id INT NOT NULL, generated_date DATETIME NOT NULL, deadline DATETIME NOT NULL, completed_date DATETIME DEFAULT NULL, mark DOUBLE PRECISION DEFAULT NULL, completed TINYINT(1) NOT NULL, progression DOUBLE PRECISION NOT NULL, INDEX IDX_4D6A006B853CD175 (quiz_id), INDEX IDX_4D6A006B94221246 (assigner_id), INDEX IDX_4D6A006B150A48F1 (chef_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `option` (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, option_text VARCHAR(255) NOT NULL, is_correct TINYINT(1) NOT NULL, feedback LONGTEXT NOT NULL, is_selected TINYINT(1) NOT NULL, INDEX IDX_5A8600B01E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, quiz_id INT NOT NULL, question_text LONGTEXT NOT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, trainer_id INT NOT NULL, type VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, case_scenario LONGTEXT NOT NULL, is_approved TINYINT(1) NOT NULL, INDEX IDX_A412FA92FB08EDF6 (trainer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE assigned_quiz ADD CONSTRAINT FK_4D6A006B853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE assigned_quiz ADD CONSTRAINT FK_4D6A006B94221246 FOREIGN KEY (assigner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE assigned_quiz ADD CONSTRAINT FK_4D6A006B150A48F1 FOREIGN KEY (chef_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `option` ADD CONSTRAINT FK_5A8600B01E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92FB08EDF6 FOREIGN KEY (trainer_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE assigned_quiz DROP FOREIGN KEY FK_4D6A006B853CD175');
        $this->addSql('ALTER TABLE assigned_quiz DROP FOREIGN KEY FK_4D6A006B94221246');
        $this->addSql('ALTER TABLE assigned_quiz DROP FOREIGN KEY FK_4D6A006B150A48F1');
        $this->addSql('ALTER TABLE `option` DROP FOREIGN KEY FK_5A8600B01E27F6BF');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92FB08EDF6');
        $this->addSql('DROP TABLE assigned_quiz');
        $this->addSql('DROP TABLE `option`');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
    }
}
