<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240825070156 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Schema retrieved thanks to this command : sqlite3 var/data.db .schema > schema.sql
        if (!$schema->hasTable('newsletter')) {
            $sql = <<<SQL
CREATE TABLE category (
    id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE TABLE recipient (
    id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    short VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE TABLE link (
    id VARCHAR(255) NOT NULL,
    category_id VARCHAR(255) NOT NULL,
    description VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL,
    date DATETIME NOT NULL,
    is_favorite TINYINT(1) DEFAULT 0,
    note MEDIUMTEXT DEFAULT NULL,
    PRIMARY KEY(id),
    CONSTRAINT FK_36AC99F112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE INDEX IDX_36AC99F112469DE2 ON link (category_id);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE TABLE link_recipient (
    link_id VARCHAR(255) NOT NULL,
    recipient_id VARCHAR(255) NOT NULL,
    PRIMARY KEY(link_id, recipient_id),
    CONSTRAINT FK_8A712B3DADA40271 FOREIGN KEY (link_id) REFERENCES link (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_8A712B3DE92F8F78 FOREIGN KEY (recipient_id) REFERENCES recipient (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE INDEX IDX_8A712B3DADA40271 ON link_recipient (link_id);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE INDEX IDX_8A712B3DE92F8F78 ON link_recipient (recipient_id);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE TABLE image (
    id VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE TABLE image_recipient (
    image_id VARCHAR(255) NOT NULL,
    recipient_id VARCHAR(255) NOT NULL,
    PRIMARY KEY(image_id, recipient_id),
    CONSTRAINT FK_B086201D3DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_B086201DE92F8F78 FOREIGN KEY (recipient_id) REFERENCES recipient (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE INDEX IDX_B086201D3DA5256D ON image_recipient (image_id);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE INDEX IDX_B086201DE92F8F78 ON image_recipient (recipient_id);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE TABLE newsletter (
    id VARCHAR(255) NOT NULL,
    first_link_id VARCHAR(255) NOT NULL,
    last_link_id VARCHAR(255) NOT NULL,
    recipient_id VARCHAR(255) NOT NULL,
    date DATETIME NOT NULL,
    PRIMARY KEY(id),
    CONSTRAINT FK_7E8585C8BE286E8D FOREIGN KEY (first_link_id) REFERENCES link (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_7E8585C8CB283E39 FOREIGN KEY (last_link_id) REFERENCES link (id) NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_7E8585C8E92F8F78 FOREIGN KEY (recipient_id) REFERENCES recipient (id) NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE UNIQUE INDEX UNIQ_7E8585C8E92F8F78 ON newsletter (recipient_id);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE TABLE newsletter_image (
    newsletter_id VARCHAR(255) NOT NULL,
    image_id VARCHAR(255) NOT NULL,
    PRIMARY KEY(newsletter_id, image_id),
    CONSTRAINT FK_4F5048AB22DB1917 FOREIGN KEY (newsletter_id) REFERENCES newsletter (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE,
    CONSTRAINT FK_4F5048AB3DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE INDEX IDX_4F5048AB22DB1917 ON newsletter_image (newsletter_id);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE INDEX IDX_4F5048AB3DA5256D ON newsletter_image (image_id);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE TABLE newsletter_category (
    newsletter_id VARCHAR(255) NOT NULL,
    category_id VARCHAR(255) NOT NULL,
    PRIMARY KEY(newsletter_id, category_id),
    CONSTRAINT FK_DB4EDFAB22DB1917 FOREIGN KEY (newsletter_id) REFERENCES newsletter (id),
    CONSTRAINT FK_DB4EDFAB12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE INDEX IDX_DB4EDFAB22DB1917 ON newsletter_category (newsletter_id);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE INDEX IDX_DB4EDFAB12469DE2 ON newsletter_category (category_id);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE TABLE tag (
    id VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY(id)
);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE TABLE link_tag (
    link_id VARCHAR(255) NOT NULL,
    tag_id VARCHAR(255) NOT NULL,
    PRIMARY KEY(link_id, tag_id),
    CONSTRAINT FK_4FF23AB8ADA40271 FOREIGN KEY (link_id) REFERENCES link (id),
    CONSTRAINT FK_4FF23AB8BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id)
);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE INDEX IDX_4FF23AB8ADA40271 ON link_tag (link_id);
SQL;
            $this->addSql($sql);

            $sql = <<<SQL
CREATE INDEX IDX_4FF23AB8BAD26311 ON link_tag (tag_id);
SQL;
            $this->addSql($sql);
        }
    }
}
