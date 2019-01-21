<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190120194614 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE activity_counter CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE crimes_committed crimes_committed INT NOT NULL');
        $this->addSql('ALTER TABLE activity_counter ADD CONSTRAINT FK_C170E298A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE activity_counter ADD CONSTRAINT FK_C170E298D331F5B5 FOREIGN KEY (squadron_id) REFERENCES squadron (id)');
        $this->addSql('CREATE INDEX IDX_C170E298A76ED395 ON activity_counter (user_id)');
        $this->addSql('CREATE INDEX IDX_C170E298D331F5B5 ON activity_counter (squadron_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE activity_counter DROP FOREIGN KEY FK_C170E298A76ED395');
        $this->addSql('ALTER TABLE activity_counter DROP FOREIGN KEY FK_C170E298D331F5B5');
        $this->addSql('DROP INDEX IDX_C170E298A76ED395 ON activity_counter');
        $this->addSql('DROP INDEX IDX_C170E298D331F5B5 ON activity_counter');
        $this->addSql('ALTER TABLE activity_counter CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE crimes_committed crimes_committed INT DEFAULT NULL');
    }
}
