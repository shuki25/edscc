<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190104022933 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE edmc (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, entered_at DATETIME NOT NULL, processed_flag TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_668A9ED9A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE edmc ADD CONSTRAINT FK_668A9ED9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD apikey VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE verify_token DROP FOREIGN KEY FK_98E46A9BA76ED395');
        $this->addSql('ALTER TABLE verify_token ADD CONSTRAINT FK_98E46A9BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE edmc');
        $this->addSql('ALTER TABLE user DROP apikey');
        $this->addSql('ALTER TABLE verify_token DROP FOREIGN KEY FK_98E46A9BA76ED395');
        $this->addSql('ALTER TABLE verify_token ADD CONSTRAINT FK_98E46A9BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }
}
