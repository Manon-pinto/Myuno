<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325110355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE carte (id INT AUTO_INCREMENT NOT NULL, couleur VARCHAR(10) NOT NULL, valeur VARCHAR(10) NOT NULL, joueur_id INT DEFAULT NULL, INDEX IDX_BAD4FFFDA9E2D76C (joueur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, statut VARCHAR(20) NOT NULL, direction INT NOT NULL, tour_actuel INT NOT NULL, pile_depioche INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE joueur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(50) NOT NULL, est_humain TINYINT NOT NULL, position INT NOT NULL, partie_id INT DEFAULT NULL, INDEX IDX_FD71A9C5E075F7A4 (partie_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE pile (id INT AUTO_INCREMENT NOT NULL, couleur_sommet VARCHAR(20) NOT NULL, valeur_sommet VARCHAR(10) NOT NULL, game_id INT NOT NULL, UNIQUE INDEX UNIQ_F9ED3E53E48FD905 (game_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE carte ADD CONSTRAINT FK_BAD4FFFDA9E2D76C FOREIGN KEY (joueur_id) REFERENCES joueur (id)');
        $this->addSql('ALTER TABLE joueur ADD CONSTRAINT FK_FD71A9C5E075F7A4 FOREIGN KEY (partie_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE pile ADD CONSTRAINT FK_F9ED3E53E48FD905 FOREIGN KEY (game_id) REFERENCES game (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE carte DROP FOREIGN KEY FK_BAD4FFFDA9E2D76C');
        $this->addSql('ALTER TABLE joueur DROP FOREIGN KEY FK_FD71A9C5E075F7A4');
        $this->addSql('ALTER TABLE pile DROP FOREIGN KEY FK_F9ED3E53E48FD905');
        $this->addSql('DROP TABLE carte');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE joueur');
        $this->addSql('DROP TABLE pile');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
