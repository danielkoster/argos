<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210108152640 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE episode (id INT AUTO_INCREMENT NOT NULL, tv_show_id INT NOT NULL, download_link VARCHAR(255) NOT NULL, season_number INT NOT NULL, episode_number INT NOT NULL, quality INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_DDAA1CDA5E3A35BB (tv_show_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE episode_candidate (id INT AUTO_INCREMENT NOT NULL, tv_show_id INT NOT NULL, download_link VARCHAR(255) NOT NULL, season_number INT NOT NULL, episode_number INT NOT NULL, quality INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_7670187D5E3A35BB (tv_show_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE feed_item (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, link VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, checksum VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tv_show (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, follow_from_season INT NOT NULL, follow_from_episode INT NOT NULL, minimum_quality INT NOT NULL, high_quality_waiting_time INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE episode ADD CONSTRAINT FK_DDAA1CDA5E3A35BB FOREIGN KEY (tv_show_id) REFERENCES tv_show (id)');
        $this->addSql('ALTER TABLE episode_candidate ADD CONSTRAINT FK_7670187D5E3A35BB FOREIGN KEY (tv_show_id) REFERENCES tv_show (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE episode DROP FOREIGN KEY FK_DDAA1CDA5E3A35BB');
        $this->addSql('ALTER TABLE episode_candidate DROP FOREIGN KEY FK_7670187D5E3A35BB');
        $this->addSql('DROP TABLE episode');
        $this->addSql('DROP TABLE episode_candidate');
        $this->addSql('DROP TABLE feed_item');
        $this->addSql('DROP TABLE tv_show');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
