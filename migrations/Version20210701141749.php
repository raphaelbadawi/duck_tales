<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210701141749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_7BA2F5EB12CF4A47');
        $this->addSql('CREATE TEMPORARY TABLE __temp__api_token AS SELECT id, duck_id, token, expires_at FROM api_token');
        $this->addSql('DROP TABLE api_token');
        $this->addSql('CREATE TABLE api_token (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, duck_id INTEGER NOT NULL, token VARCHAR(255) NOT NULL COLLATE BINARY, expires_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , CONSTRAINT FK_7BA2F5EB12CF4A47 FOREIGN KEY (duck_id) REFERENCES duck (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO api_token (id, duck_id, token, expires_at) SELECT id, duck_id, token, expires_at FROM __temp__api_token');
        $this->addSql('DROP TABLE __temp__api_token');
        $this->addSql('CREATE INDEX IDX_7BA2F5EB12CF4A47 ON api_token (duck_id)');
        $this->addSql('DROP INDEX IDX_EE49F7F1D3950CA9');
        $this->addSql('DROP INDEX IDX_EE49F7F112CF4A47');
        $this->addSql('CREATE TEMPORARY TABLE __temp__duck_quack AS SELECT duck_id, quack_id FROM duck_quack');
        $this->addSql('DROP TABLE duck_quack');
        $this->addSql('CREATE TABLE duck_quack (duck_id INTEGER NOT NULL, quack_id INTEGER NOT NULL, PRIMARY KEY(duck_id, quack_id), CONSTRAINT FK_EE49F7F112CF4A47 FOREIGN KEY (duck_id) REFERENCES duck (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EE49F7F1D3950CA9 FOREIGN KEY (quack_id) REFERENCES quack (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO duck_quack (duck_id, quack_id) SELECT duck_id, quack_id FROM __temp__duck_quack');
        $this->addSql('DROP TABLE __temp__duck_quack');
        $this->addSql('CREATE INDEX IDX_EE49F7F1D3950CA9 ON duck_quack (quack_id)');
        $this->addSql('CREATE INDEX IDX_EE49F7F112CF4A47 ON duck_quack (duck_id)');
        $this->addSql('DROP INDEX IDX_83D44F6F12CF4A47');
        $this->addSql('DROP INDEX IDX_83D44F6F3D8E604F');
        $this->addSql('CREATE TEMPORARY TABLE __temp__quack AS SELECT id, parent, duck_id, content, created_at, picture FROM quack');
        $this->addSql('DROP TABLE quack');
        $this->addSql('CREATE TABLE quack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent INTEGER DEFAULT NULL, duck_id INTEGER DEFAULT NULL, content CLOB NOT NULL COLLATE BINARY, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , picture VARCHAR(255) DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_83D44F6F3D8E604F FOREIGN KEY (parent) REFERENCES quack (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_83D44F6F12CF4A47 FOREIGN KEY (duck_id) REFERENCES duck (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO quack (id, parent, duck_id, content, created_at, picture) SELECT id, parent, duck_id, content, created_at, picture FROM __temp__quack');
        $this->addSql('DROP TABLE __temp__quack');
        $this->addSql('CREATE INDEX IDX_83D44F6F12CF4A47 ON quack (duck_id)');
        $this->addSql('CREATE INDEX IDX_83D44F6F3D8E604F ON quack (parent)');
        $this->addSql('DROP INDEX IDX_9793B2CAF0CAEBB8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__quack_history AS SELECT id, original_quack_id, content, created_at, picture FROM quack_history');
        $this->addSql('DROP TABLE quack_history');
        $this->addSql('CREATE TABLE quack_history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, original_quack_id INTEGER DEFAULT NULL, content VARCHAR(255) NOT NULL COLLATE BINARY, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , picture VARCHAR(255) DEFAULT NULL COLLATE BINARY, CONSTRAINT FK_9793B2CAF0CAEBB8 FOREIGN KEY (original_quack_id) REFERENCES quack (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO quack_history (id, original_quack_id, content, created_at, picture) SELECT id, original_quack_id, content, created_at, picture FROM __temp__quack_history');
        $this->addSql('DROP TABLE __temp__quack_history');
        $this->addSql('CREATE INDEX IDX_9793B2CAF0CAEBB8 ON quack_history (original_quack_id)');
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
        $this->addSql('DROP INDEX IDX_7BA2F5EB12CF4A47');
        $this->addSql('CREATE TEMPORARY TABLE __temp__api_token AS SELECT id, duck_id, token, expires_at FROM api_token');
        $this->addSql('DROP TABLE api_token');
        $this->addSql('CREATE TABLE api_token (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, duck_id INTEGER NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        )');
        $this->addSql('INSERT INTO api_token (id, duck_id, token, expires_at) SELECT id, duck_id, token, expires_at FROM __temp__api_token');
        $this->addSql('DROP TABLE __temp__api_token');
        $this->addSql('CREATE INDEX IDX_7BA2F5EB12CF4A47 ON api_token (duck_id)');
        $this->addSql('DROP INDEX IDX_EE49F7F112CF4A47');
        $this->addSql('DROP INDEX IDX_EE49F7F1D3950CA9');
        $this->addSql('CREATE TEMPORARY TABLE __temp__duck_quack AS SELECT duck_id, quack_id FROM duck_quack');
        $this->addSql('DROP TABLE duck_quack');
        $this->addSql('CREATE TABLE duck_quack (duck_id INTEGER NOT NULL, quack_id INTEGER NOT NULL, PRIMARY KEY(duck_id, quack_id))');
        $this->addSql('INSERT INTO duck_quack (duck_id, quack_id) SELECT duck_id, quack_id FROM __temp__duck_quack');
        $this->addSql('DROP TABLE __temp__duck_quack');
        $this->addSql('CREATE INDEX IDX_EE49F7F112CF4A47 ON duck_quack (duck_id)');
        $this->addSql('CREATE INDEX IDX_EE49F7F1D3950CA9 ON duck_quack (quack_id)');
        $this->addSql('DROP INDEX IDX_83D44F6F3D8E604F');
        $this->addSql('DROP INDEX IDX_83D44F6F12CF4A47');
        $this->addSql('CREATE TEMPORARY TABLE __temp__quack AS SELECT id, parent, duck_id, content, created_at, picture FROM quack');
        $this->addSql('DROP TABLE quack');
        $this->addSql('CREATE TABLE quack (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, parent INTEGER DEFAULT NULL, duck_id INTEGER DEFAULT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , picture VARCHAR(255) DEFAULT NULL, is_old BOOLEAN NOT NULL)');
        $this->addSql('INSERT INTO quack (id, parent, duck_id, content, created_at, picture) SELECT id, parent, duck_id, content, created_at, picture FROM __temp__quack');
        $this->addSql('DROP TABLE __temp__quack');
        $this->addSql('CREATE INDEX IDX_83D44F6F3D8E604F ON quack (parent)');
        $this->addSql('CREATE INDEX IDX_83D44F6F12CF4A47 ON quack (duck_id)');
        $this->addSql('DROP INDEX IDX_9793B2CAF0CAEBB8');
        $this->addSql('CREATE TEMPORARY TABLE __temp__quack_history AS SELECT id, original_quack_id, content, created_at, picture FROM quack_history');
        $this->addSql('DROP TABLE quack_history');
        $this->addSql('CREATE TABLE quack_history (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, original_quack_id INTEGER DEFAULT NULL, content VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL --(DC2Type:datetime_immutable)
        , picture VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO quack_history (id, original_quack_id, content, created_at, picture) SELECT id, original_quack_id, content, created_at, picture FROM __temp__quack_history');
        $this->addSql('DROP TABLE __temp__quack_history');
        $this->addSql('CREATE INDEX IDX_9793B2CAF0CAEBB8 ON quack_history (original_quack_id)');
        $this->addSql('DROP INDEX IDX_389B783D3950CA9');
        $this->addSql('CREATE TEMPORARY TABLE __temp__tag AS SELECT id, quack_id, content FROM tag');
        $this->addSql('DROP TABLE tag');
        $this->addSql('CREATE TABLE tag (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, quack_id INTEGER NOT NULL, content VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO tag (id, quack_id, content) SELECT id, quack_id, content FROM __temp__tag');
        $this->addSql('DROP TABLE __temp__tag');
        $this->addSql('CREATE INDEX IDX_389B783D3950CA9 ON tag (quack_id)');
    }
}
