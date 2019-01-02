<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190102012014 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD email_verify VARCHAR(1) DEFAULT \'N\' NOT NULL, CHANGE google_flag google_flag VARCHAR(1) DEFAULT \'N\' NOT NULL, CHANGE gravatar_flag gravatar_flag VARCHAR(1) DEFAULT \'N\' NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user DROP email_verify, CHANGE google_flag google_flag VARCHAR(1) DEFAULT NULL COLLATE utf8_general_ci, CHANGE gravatar_flag gravatar_flag VARCHAR(1) DEFAULT NULL COLLATE utf8_general_ci');
    }
}
