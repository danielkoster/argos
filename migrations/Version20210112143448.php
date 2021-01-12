<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210112143448 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE episode ADD is_proper TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE episode_candidate ADD is_proper TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE feed_item DROP is_proper');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE episode DROP is_proper');
        $this->addSql('ALTER TABLE episode_candidate DROP is_proper');
        $this->addSql('ALTER TABLE feed_item ADD is_proper TINYINT(1) NOT NULL');
    }
}
