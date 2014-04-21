<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/04/21 03:53:02
 */
class Version20140421155256 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_user_role_creation (
                id INT IDENTITY NOT NULL, 
                user_id INT NOT NULL, 
                creation_date DATETIME2(6) NOT NULL, 
                userRole_id INT NOT NULL, 
                PRIMARY KEY (id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_709FE2E85DFE78E ON claro_user_role_creation (userRole_id)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_709FE2EA76ED395 ON claro_user_role_creation (user_id) 
            WHERE user_id IS NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_role_creation 
            ADD CONSTRAINT FK_709FE2E85DFE78E FOREIGN KEY (userRole_id) 
            REFERENCES claro_role (id)
        ");
        $this->addSql("
            ALTER TABLE claro_user_role_creation 
            ADD CONSTRAINT FK_709FE2EA76ED395 FOREIGN KEY (user_id) 
            REFERENCES claro_user (id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_user_role_creation
        ");
    }
}