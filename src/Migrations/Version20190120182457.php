<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190120182457 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commander ADD debt INT DEFAULT NULL, CHANGE cash credits BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE earning_history ADD CONSTRAINT FK_4616DE45A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE earning_history ADD CONSTRAINT FK_4616DE45D331F5B5 FOREIGN KEY (squadron_id) REFERENCES squadron (id)');
        $this->addSql('ALTER TABLE earning_history ADD CONSTRAINT FK_4616DE45A4E0229D FOREIGN KEY (earning_type_id) REFERENCES earning_type (id)');
        $this->addSql('CREATE INDEX IDX_4616DE45A76ED395 ON earning_history (user_id)');
        $this->addSql('CREATE INDEX IDX_4616DE45D331F5B5 ON earning_history (squadron_id)');
        $this->addSql('CREATE INDEX IDX_4616DE45A4E0229D ON earning_history (earning_type_id)');
        $this->addSql('ALTER TABLE earning_type CHANGE name name VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commander DROP debt, CHANGE credits cash BIGINT DEFAULT NULL');
        $this->addSql('ALTER TABLE earning_history DROP FOREIGN KEY FK_4616DE45A76ED395');
        $this->addSql('ALTER TABLE earning_history DROP FOREIGN KEY FK_4616DE45D331F5B5');
        $this->addSql('ALTER TABLE earning_history DROP FOREIGN KEY FK_4616DE45A4E0229D');
        $this->addSql('DROP INDEX IDX_4616DE45A76ED395 ON earning_history');
        $this->addSql('DROP INDEX IDX_4616DE45D331F5B5 ON earning_history');
        $this->addSql('DROP INDEX IDX_4616DE45A4E0229D ON earning_history');
        $this->addSql('ALTER TABLE earning_type CHANGE name name VARCHAR(20) DEFAULT \'\' NOT NULL COLLATE utf8_general_ci');
    }
}
