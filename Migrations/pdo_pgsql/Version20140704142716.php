<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/04 02:27:18
 */
class Version20140704142716 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP CONSTRAINT FK_D9028545727ACA70
        ");
        $this->addSql("
            DROP INDEX IDX_D9028545727ACA70
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP parent_id
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP discr
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP lft
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP lvl
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP rgt
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            DROP root
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD parent_id INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD discr VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD lft INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD lvl INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD rgt INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD root INT DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_workspace 
            ADD CONSTRAINT FK_D9028545727ACA70 FOREIGN KEY (parent_id) 
            REFERENCES claro_workspace (id) 
            ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
        $this->addSql("
            CREATE INDEX IDX_D9028545727ACA70 ON claro_workspace (parent_id)
        ");
    }
}