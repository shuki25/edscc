<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190114041010 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commander ADD combat_id INT DEFAULT NULL, ADD trade_id INT DEFAULT NULL, ADD explore_id INT DEFAULT NULL, ADD federation_id INT DEFAULT NULL, ADD empire_id INT DEFAULT NULL, ADD cqc_id INT DEFAULT NULL, DROP combat, DROP trade, DROP explore, DROP cqc, DROP federation, DROP empire, CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE asset asset BIGINT NOT NULL, CHANGE cash cash BIGINT NOT NULL, CHANGE user_id user_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE commander ADD CONSTRAINT FK_42D318BA9D86650F FOREIGN KEY (user_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE commander ADD CONSTRAINT FK_42D318BAFC7EEDB8 FOREIGN KEY (combat_id) REFERENCES rank (id)');
        $this->addSql('ALTER TABLE commander ADD CONSTRAINT FK_42D318BAC2D9760 FOREIGN KEY (trade_id) REFERENCES rank (id)');
        $this->addSql('ALTER TABLE commander ADD CONSTRAINT FK_42D318BA903164BD FOREIGN KEY (explore_id) REFERENCES rank (id)');
        $this->addSql('ALTER TABLE commander ADD CONSTRAINT FK_42D318BA6A03EFC5 FOREIGN KEY (federation_id) REFERENCES rank (id)');
        $this->addSql('ALTER TABLE commander ADD CONSTRAINT FK_42D318BA6E6A432A FOREIGN KEY (empire_id) REFERENCES rank (id)');
        $this->addSql('ALTER TABLE commander ADD CONSTRAINT FK_42D318BA4B8351E7 FOREIGN KEY (cqc_id) REFERENCES rank (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_42D318BA9D86650F ON commander (user_id_id)');
        $this->addSql('CREATE INDEX IDX_42D318BAFC7EEDB8 ON commander (combat_id)');
        $this->addSql('CREATE INDEX IDX_42D318BAC2D9760 ON commander (trade_id)');
        $this->addSql('CREATE INDEX IDX_42D318BA903164BD ON commander (explore_id)');
        $this->addSql('CREATE INDEX IDX_42D318BA6A03EFC5 ON commander (federation_id)');
        $this->addSql('CREATE INDEX IDX_42D318BA6E6A432A ON commander (empire_id)');
        $this->addSql('CREATE INDEX IDX_42D318BA4B8351E7 ON commander (cqc_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE commander DROP FOREIGN KEY FK_42D318BA9D86650F');
        $this->addSql('ALTER TABLE commander DROP FOREIGN KEY FK_42D318BAFC7EEDB8');
        $this->addSql('ALTER TABLE commander DROP FOREIGN KEY FK_42D318BAC2D9760');
        $this->addSql('ALTER TABLE commander DROP FOREIGN KEY FK_42D318BA903164BD');
        $this->addSql('ALTER TABLE commander DROP FOREIGN KEY FK_42D318BA6A03EFC5');
        $this->addSql('ALTER TABLE commander DROP FOREIGN KEY FK_42D318BA6E6A432A');
        $this->addSql('ALTER TABLE commander DROP FOREIGN KEY FK_42D318BA4B8351E7');
        $this->addSql('DROP INDEX UNIQ_42D318BA9D86650F ON commander');
        $this->addSql('DROP INDEX IDX_42D318BAFC7EEDB8 ON commander');
        $this->addSql('DROP INDEX IDX_42D318BAC2D9760 ON commander');
        $this->addSql('DROP INDEX IDX_42D318BA903164BD ON commander');
        $this->addSql('DROP INDEX IDX_42D318BA6A03EFC5 ON commander');
        $this->addSql('DROP INDEX IDX_42D318BA6E6A432A ON commander');
        $this->addSql('DROP INDEX IDX_42D318BA4B8351E7 ON commander');
        $this->addSql('ALTER TABLE commander ADD user_id INT DEFAULT NULL, ADD combat SMALLINT DEFAULT NULL, ADD trade SMALLINT DEFAULT NULL, ADD explore SMALLINT DEFAULT NULL, ADD cqc SMALLINT DEFAULT NULL, ADD federation SMALLINT DEFAULT NULL, ADD empire SMALLINT DEFAULT NULL, DROP user_id_id, DROP combat_id, DROP trade_id, DROP explore_id, DROP federation_id, DROP empire_id, DROP cqc_id, CHANGE id id INT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE asset asset BIGINT DEFAULT NULL, CHANGE cash cash BIGINT DEFAULT NULL');
    }
}
