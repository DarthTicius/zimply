<?php
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "Environment variables:\n";
echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
echo "DB_USER: " . ($_ENV['DB_USER'] ?? 'not set') . "\n";
echo "DB_NAME: " . ($_ENV['DB_NAME'] ?? 'not set') . "\n";

echo "\nTesting MySQL connection methods:\n";

try {
	$pdo = new PDO(
		"mysql:unix_socket=/var/run/mysqld/mysqld.sock;charset=utf8",
		$_ENV['DB_USER'],
		$_ENV['DB_PASSWORD']
	);
	echo "Socket connection successful!\n";
} catch (PDOException $e) {
	echo "Socket connection failed: " . $e->getMessage() . "\n";
}

// Try TCP
try {
	$pdo = new PDO(
		"mysql:host=" . $_ENV['DB_HOST'] . ";port=3306;charset=utf8",
		$_ENV['DB_USER'],
		$_ENV['DB_PASSWORD']
	);
	echo "TCP connection successful!\n";
} catch (PDOException $e) {
	echo "TCP connection failed: " . $e->getMessage() . "\n";
}
