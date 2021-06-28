<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210628081631 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_83D44F6F12CF4A47');
        $this->addSql('CREATE TEMPORARY TABLE __temp__quack AS SELECT id, duck_id, content, created_at, picture, old_id, is_old FROM quack');
        $this->addSql('DROP TABLE quack');
        $this->addSql('CREATE TABLE quack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, duck_id INTEGER DEFAULT NULL, content CLOB NOT NULL COLLATE BINARY, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , picture VARCHAR(255) DEFAULT NULL COLLATE BINARY, old_id INTEGER DEFAULT NULL, is_old BOOLEAN NOT NULL, CONSTRAINT FK_83D44F6F12CF4A47 FOREIGN KEY (duck_id) REFERENCES duck (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO quack (id, duck_id, content, created_at, picture, old_id, is_old) SELECT id, duck_id, content, created_at, picture, old_id, is_old FROM __temp__quack');
        $this->addSql('DROP TABLE __temp__quack');
        $this->addSql('CREATE INDEX IDX_83D44F6F12CF4A47 ON quack (duck_id)');
        $this->addSql('DROP INDEX IDX_389B783D3950CA9');
        $this->addSql('CREATE TEMPORARY TABLE __temp__tag AS SELECT id, quack_id, content FROM tag');
        $this->addSql('DROP TABLE tag');
        $this->addSql('CREATE TABLE tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quack_id INTEGER NOT NULL, content VARCHAR(255) NOT NULL COLLATE BINARY, CONSTRAINT FK_389B783D3950CA9 FOREIGN KEY (quack_id) REFERENCES quack (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO tag (id, quack_id, content) SELECT id, quack_id, content FROM __temp__tag');
        $this->addSql('DROP TABLE __temp__tag');
        $this->addSql('CREATE INDEX IDX_389B783D3950CA9 ON tag (quack_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_83D44F6F12CF4A47');
        $this->addSql('CREATE TEMPORARY TABLE __temp__quack AS SELECT id, duck_id, content, created_at, picture, old_id, is_old FROM quack');
        $this->addSql('DROP TABLE quack');
        $this->addSql('CREATE TABLE quack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, duck_id INTEGER DEFAULT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , picture VARCHAR(255) DEFAULT NULL, old_id INTEGER DEFAULT NULL, is_old BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO quack (id, duck_id, content, created_at, picture, old_id, is_old) SELECT id, duck_id, content, created_at, picture, old_id, is_old FROM __temp__quack');
        $this->addSql('DROP TABLE __temp__quack');
        $this->addSql('CREATE INDEX IDX_83D44F6F12CF4A47 ON quack (duck_id)');
        $this->addSql('DROP INDEX IDX_389B783D3950CA9');
        $this->addSql('CREATE TEMPORARY TABLE __temp__tag AS SELECT id, quack_id, content FROM tag');
        $this->addSql('DROP TABLE tag');
        $this->addSql('CREATE TABLE tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quack_id INTEGER NOT NULL, content VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO tag (id, quack_id, content) SELECT id, quack_id, content FROM __temp__tag');
        $this->addSql('DROP TABLE __temp__tag');
        $this->addSql('CREATE INDEX IDX_389B783D3950CA9 ON tag (quack_id)');
    }
}
