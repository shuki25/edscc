<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190102184912 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE squadron ADD admin_id INT NOT NULL, ADD description LONGTEXT DEFAULT NULL, ADD welcome_message LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE squadron ADD CONSTRAINT FK_5901B757642B8210 FOREIGN KEY (admin_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5901B757642B8210 ON squadron (admin_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE squadron DROP FOREIGN KEY FK_5901B757642B8210');
        $this->addSql('DROP INDEX UNIQ_5901B757642B8210 ON squadron');
        $this->addSql('ALTER TABLE squadron DROP admin_id, DROP description, DROP welcome_message');
    }
}
