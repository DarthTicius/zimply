<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
	private static $instance = null;
	private PDO $pdo;

	private function __construct()
	{
		try {
			$dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";charset=utf8";
			$this->pdo = new PDO(
				$dsn,
				$_ENV['DB_USER'],
				$_ENV['DB_PASSWORD']
			);
			$this->pdo->exec("SET time_zone = '+02:00'");

			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

			$this->pdo->exec("CREATE DATABASE IF NOT EXISTS " . $_ENV['DB_NAME']);
			$this->pdo->exec("USE " . $_ENV['DB_NAME']);

			$this->createTables();
		} catch (PDOException $e) {
			die("Connection failed: " . $e->getMessage());
		}
	}

	private function createTables(): void
	{
		$this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                reset_token VARCHAR(255) DEFAULT NULL,
                reset_token_expiry DATETIME DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

		$this->pdo->exec("
            CREATE TABLE IF NOT EXISTS csrf_tokens (
                token VARCHAR(255) PRIMARY KEY,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

		$this->pdo->exec("
            CREATE TABLE IF NOT EXISTS rate_limits (
                ip_address VARCHAR(45),
                endpoint VARCHAR(255),
                requests INT DEFAULT 1,
                last_request TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (ip_address, endpoint)
            )
        ");
	}

	public static function getInstance(): self
	{
		if (self::$instance === null) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function getConnection(): PDO
	{
		return $this->pdo;
	}
}
