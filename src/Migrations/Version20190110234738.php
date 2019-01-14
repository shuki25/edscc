<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190110234738 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE announcement ADD created_by INT DEFAULT NULL, ADD updated_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE announcement ADD CONSTRAINT FK_4DB9D91CDE12AB56 FOREIGN KEY (created_by) REFERENCES user (id)');
        $this->addSql('ALTER TABLE announcement ADD CONSTRAINT FK_4DB9D91C16FE72E1 FOREIGN KEY (updated_by) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_4DB9D91CDE12AB56 ON announcement (created_by)');
        $this->addSql('CREATE INDEX IDX_4DB9D91C16FE72E1 ON announcement (updated_by)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE announcement DROP FOREIGN KEY FK_4DB9D91CDE12AB56');
        $this->addSql('ALTER TABLE announcement DROP FOREIGN KEY FK_4DB9D91C16FE72E1');
        $this->addSql('DROP INDEX IDX_4DB9D91CDE12AB56 ON announcement');
        $this->addSql('DROP INDEX IDX_4DB9D91C16FE72E1 ON announcement');
        $this->addSql('ALTER TABLE announcement DROP created_by, DROP updated_by');
    }
}
