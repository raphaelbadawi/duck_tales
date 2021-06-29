<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210629065221 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE duck (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL --(DC2Type:json)
        , password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, duckname VARCHAR(255) NOT NULL, google_id INTEGER DEFAULT NULL)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_538A9547E7927C74 ON duck (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_538A954790361416 ON duck (duckname)');
        $this->addSql('CREATE TABLE duck_quack (duck_id INTEGER NOT NULL, quack_id INTEGER NOT NULL, PRIMARY KEY(duck_id, quack_id))');
        $this->addSql('CREATE INDEX IDX_EE49F7F112CF4A47 ON duck_quack (duck_id)');
        $this->addSql('CREATE INDEX IDX_EE49F7F1D3950CA9 ON duck_quack (quack_id)');
        $this->addSql('CREATE TABLE quack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent INTEGER DEFAULT NULL, duck_id INTEGER DEFAULT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , picture VARCHAR(255) DEFAULT NULL, old_id INTEGER DEFAULT NULL, is_old BOOLEAN NOT NULL)');
        $this->addSql('CREATE INDEX IDX_83D44F6F3D8E604F ON quack (parent)');
        $this->addSql('CREATE INDEX IDX_83D44F6F12CF4A47 ON quack (duck_id)');
        $this->addSql('CREATE TABLE tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quack_id INTEGER NOT NULL, content VARCHAR(255) NOT NULL)');
        $this->addSql('CREATE INDEX IDX_389B783D3950CA9 ON tag (quack_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE duck');
        $this->addSql('DROP TABLE duck_quack');
        $this->addSql('DROP TABLE quack');
        $this->addSql('DROP TABLE tag');
    }
}
