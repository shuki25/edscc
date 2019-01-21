<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190119180457 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commander DROP FOREIGN KEY FK_42D318BA9D86650F');
        $this->addSql('DROP INDEX UNIQ_42D318BA9D86650F ON commander');
        $this->addSql('ALTER TABLE commander CHANGE user_id_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commander ADD CONSTRAINT FK_42D318BAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_42D318BAA76ED395 ON commander (user_id)');
        $this->addSql('ALTER TABLE import_queue DROP FOREIGN KEY FK_92A8D0AD9D86650F');
        $this->addSql('DROP INDEX IDX_92A8D0AD9D86650F ON import_queue');
        $this->addSql('ALTER TABLE import_queue CHANGE user_id_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE import_queue ADD CONSTRAINT FK_92A8D0ADA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_92A8D0ADA76ED395 ON import_queue (user_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commander DROP FOREIGN KEY FK_42D318BAA76ED395');
        $this->addSql('DROP INDEX UNIQ_42D318BAA76ED395 ON commander');
        $this->addSql('ALTER TABLE commander CHANGE user_id user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commander ADD CONSTRAINT FK_42D318BA9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_42D318BA9D86650F ON commander (user_id_id)');
        $this->addSql('ALTER TABLE import_queue DROP FOREIGN KEY FK_92A8D0ADA76ED395');
        $this->addSql('DROP INDEX IDX_92A8D0ADA76ED395 ON import_queue');
        $this->addSql('ALTER TABLE import_queue CHANGE user_id user_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE import_queue ADD CONSTRAINT FK_92A8D0AD9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_92A8D0AD9D86650F ON import_queue (user_id_id)');
    }
}
