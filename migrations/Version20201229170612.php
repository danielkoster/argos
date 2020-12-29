<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201229170612 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE episode (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, show_id INTEGER NOT NULL, download_link VARCHAR(255) NOT NULL, season_number INTEGER NOT NULL, episode_number INTEGER NOT NULL, quality INTEGER NOT NULL, is_downloaded BOOLEAN NOT NULL, created_at DATETIME NOT NULL)');
        $this->addSql('CREATE INDEX IDX_DDAA1CDAD0C1FC64 ON episode (show_id)');
        $this->addSql('CREATE TABLE show (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, follow_from_season INTEGER NOT NULL, follow_from_episode INTEGER NOT NULL, minimum_quality INTEGER NOT NULL, high_quality_waiting_time INTEGER NOT NULL)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE episode');
        $this->addSql('DROP TABLE show');
    }
}
