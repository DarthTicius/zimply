<?php

namespace App\Services;

use App\Config\Database;
use PDO;

class SecurityService
{
	private PDO $db;
	private const MAX_REQUESTS = 10;
	private const TIME_WINDOW = 60;

	public function __construct()
	{
		$this->db = Database::getInstance()->getConnection();
	}

	public function generateCsrfToken(): string
	{
		$token = bin2hex(random_bytes(32));
		$stmt = $this->db->prepare("INSERT INTO csrf_tokens (token) VALUES (?)");
		$stmt->execute([$token]);
		return $token;
	}

	public function validateCsrfToken(string $token): bool
	{

		$stmt = $this->db->prepare("
        SELECT COUNT(*) as token_count 
        FROM csrf_tokens 
        WHERE token = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
    ");
		$stmt->execute([$token]);
		$result = $stmt->fetch();

		return $result['token_count'] > 0;
	}
	public function invalidateOldCsrfTokens(): void
	{
		$stmt = $this->db->prepare("DELETE FROM csrf_tokens WHERE created_at < NOW() - INTERVAL 1 HOUR");
		$stmt->execute();
	}

	public function checkRateLimit(string $ipAddress, string $endpoint): bool
	{
		$stmt = $this->db->prepare("
            SELECT requests, UNIX_TIMESTAMP(last_request) as last_request_ts
            FROM rate_limits
            WHERE ip_address = ? AND endpoint = ?
        ");
		$stmt->execute([$ipAddress, $endpoint]);
		$limit = $stmt->fetch();

		if (!$limit) {
			$this->insertRateLimit($ipAddress, $endpoint);
			return true;
		}

		$timeSinceLastRequest = time() - $limit['last_request_ts'];
		if ($timeSinceLastRequest > self::TIME_WINDOW) {
			$this->resetRateLimit($ipAddress, $endpoint);
			return true;
		}

		if ($limit['requests'] >= self::MAX_REQUESTS) {
			return false;
		}

		$this->incrementRateLimit($ipAddress, $endpoint);
		return true;
	}

	private function insertRateLimit(string $ipAddress, string $endpoint): void
	{
		$stmt = $this->db->prepare("
            INSERT INTO rate_limits (ip_address, endpoint) 
            VALUES (?, ?)
        ");
		$stmt->execute([$ipAddress, $endpoint]);
	}

	private function resetRateLimit(string $ipAddress, string $endpoint): void
	{
		$stmt = $this->db->prepare("
            UPDATE rate_limits 
            SET requests = 1, last_request = CURRENT_TIMESTAMP
            WHERE ip_address = ? AND endpoint = ?
        ");
		$stmt->execute([$ipAddress, $endpoint]);
	}

	private function incrementRateLimit(string $ipAddress, string $endpoint): void
	{
		$stmt = $this->db->prepare("
            UPDATE rate_limits 
            SET requests = requests + 1, last_request = CURRENT_TIMESTAMP
            WHERE ip_address = ? AND endpoint = ?
        ");
		$stmt->execute([$ipAddress, $endpoint]);
	}
}
