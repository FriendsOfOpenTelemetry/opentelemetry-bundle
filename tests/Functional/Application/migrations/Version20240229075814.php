<?php

namespace FriendsOfOpenTelemetry\OpenTelemetryBundle\Tests\Functional\Application\migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240229075814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Default schema';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE dummy (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE "BINARY")');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE dummy');
    }
}
