<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250604170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add sharedTokenMaxUses and sharedTokenUses to Document';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document ADD shared_token_max_uses INT DEFAULT NULL, ADD shared_token_uses INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE document DROP shared_token_max_uses, DROP shared_token_uses');
    }
}
