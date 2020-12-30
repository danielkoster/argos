<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201230151545 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE episode (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, show_id INTEGER NOT NULL, download_link VARCHAR(255) NOT NULL, season_number INTEGER NOT NULL, episode_number INTEGER NOT NULL, quality INTEGER NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX IDX_DDAA1CDAD0C1FC64 ON episode (show_id)');
        $this->addSql('CREATE TABLE episode_candidate (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, show_id INTEGER NOT NULL, download_link VARCHAR(255) NOT NULL, season_number INTEGER NOT NULL, episode_number INTEGER NOT NULL, quality INTEGER NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX IDX_7670187DD0C1FC64 ON episode_candidate (show_id)');
        $this->addSql('CREATE TABLE show (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, follow_from_season INTEGER NOT NULL, follow_from_episode INTEGER NOT NULL, minimum_quality INTEGER NOT NULL, high_quality_waiting_time INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE episode');
        $this->addSql('DROP TABLE episode_candidate');
        $this->addSql('DROP TABLE show');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
