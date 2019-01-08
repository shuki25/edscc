<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190107205601 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE faction (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, logo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE power (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, logo VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE squadron ADD faction_id INT DEFAULT NULL, ADD power_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE squadron ADD CONSTRAINT FK_5901B7574448F8DA FOREIGN KEY (faction_id) REFERENCES faction (id)');
        $this->addSql('ALTER TABLE squadron ADD CONSTRAINT FK_5901B757AB4FC384 FOREIGN KEY (power_id) REFERENCES power (id)');
        $this->addSql('CREATE INDEX IDX_5901B7574448F8DA ON squadron (faction_id)');
        $this->addSql('CREATE INDEX IDX_5901B757AB4FC384 ON squadron (power_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE squadron DROP FOREIGN KEY FK_5901B7574448F8DA');
        $this->addSql('ALTER TABLE squadron DROP FOREIGN KEY FK_5901B757AB4FC384');
        $this->addSql('DROP TABLE faction');
        $this->addSql('DROP TABLE power');
        $this->addSql('DROP INDEX IDX_5901B7574448F8DA ON squadron');
        $this->addSql('DROP INDEX IDX_5901B757AB4FC384 ON squadron');
        $this->addSql('ALTER TABLE squadron DROP faction_id, DROP power_id');
    }
}
