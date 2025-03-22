<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250322081755 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Create users table
        $this->addSql(<<<SQL
             CREATE TABLE users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                phone_number VARCHAR(20) NOT NULL,
                subscription_type ENUM('Free', 'Premium') NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        SQL
        );

        // Create payments table
        $this->addSql(<<<SQL
             CREATE TABLE payments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                card_number VARCHAR(16) NOT NULL,
                expiration_date VARCHAR(5) NOT NULL, 
                cvv VARCHAR(4) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_payments_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE

            );
        SQL
        );

        // Create countries table
        $this->addSql(<<<SQL
            CREATE TABLE countries (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE
        );
        SQL
        );

        // Create addresses table
        $this->addSql(<<<SQL
            CREATE TABLE addresses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                address_line1 VARCHAR(255) NOT NULL,
                address_line2 VARCHAR(255) DEFAULT NULL,
                city VARCHAR(100) NOT NULL,
                postal_code VARCHAR(20) NOT NULL,
                state_province VARCHAR(100) NOT NULL,
                country_id INT NOT NULL,   -- Foreign key reference
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_addresses_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_addresses_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE RESTRICT
        );
        SQL
        );


    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL

            DROP TABLE IF EXISTS payments;
            DROP TABLE IF EXISTS addresses;
            DROP TABLE IF EXISTS users;
            DROP TABLE IF EXISTS countries;

        SQL
        );

    }
}
