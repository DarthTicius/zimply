<?php

namespace App\Services;

use App\Config\Database;
use PDO;

class AuthService
{
	private PDO $db;

	public function __construct()
	{
		date_default_timezone_set('Europe/Berlin'); // Ensure the time zone is set correctly
		$this->db = Database::getInstance()->getConnection();
	}

	public function register(array $data): array
	{
		$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
		if (!$email) {
			return ['error' => 'Invalid email'];
		}

		if (strlen($data['password']) < 8) {
			return ['error' => 'Password must be at least 8 characters'];
		}

		try {
			$stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
			$stmt->execute([$email]);
			if ($stmt->fetch()) {
				return ['error' => 'Email already exists'];
			}

			$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
			$stmt = $this->db->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
			$stmt->execute([$email, $hashedPassword]);

			return ['message' => 'User registered successfully'];
		} catch (\Exception $e) {
			return ['error' => 'Registration failed'];
		}
	}

	public function login(array $data): array
	{
		try {
			$stmt = $this->db->prepare("
                SELECT id, email, password, reset_token 
                FROM users 
                WHERE email = ?
            ");
			$stmt->execute([$data['email']]);
			$user = $stmt->fetch();

			if (!$user || !password_verify($data['password'], $user['password'])) {
				return ['error' => 'Invalid credentials'];
			}

			$_SESSION['user_id'] = $user['id'];
			$_SESSION['email'] = $user['email'];

			return [
				'message' => 'Login successful',
				'user' => [
					'id' => $user['id'],
					'email' => $user['email']
				]
			];
		} catch (\Exception $e) {
			return ['error' => 'Login failed'];
		}
	}

	public function initiatePasswordReset(array $data): array
	{
		try {
			$stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
			$stmt->execute([$data['email']]);
			$user = $stmt->fetch();

			if (!$user) {
				return ['message' => 'If the email exists, a reset link will be sent'];
			}

			$resetToken = urlencode(bin2hex(random_bytes(32)));
			$resetTokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

			$stmt = $this->db->prepare("
				UPDATE users 
				SET reset_token = :token,
					reset_token_expiry = :expiry 
				WHERE email = :email
			");

			$params = [
				':token' => $resetToken,
				':expiry' => $resetTokenExpiry,
				':email' => $data['email']
			];

			$stmt->execute($params);

			return [
				'message' => 'Password reset instructions sent',
				'debug_token' => $resetToken
			];
		} catch (\Exception $e) {
			return ['error' => 'Password reset failed'];
		}
	}

	public function resetPassword(array $data): array
	{
		$token = trim(urldecode($data['token']));
		$newPassword = password_hash($data['password'], PASSWORD_BCRYPT);

		try {
			$checkStmt = $this->db->prepare("
            SELECT reset_token, reset_token_expiry 
            FROM users 
            WHERE reset_token = :token 
            AND reset_token_expiry > CONVERT_TZ(NOW(), @@session.time_zone, '+00:00')
        ");
			$checkStmt->execute([':token' => $token]);
			$tokenData = $checkStmt->fetch(PDO::FETCH_ASSOC);

			if (!$tokenData) {
				return ['error' => 'Invalid or expired reset token.'];
			}

			$updateStmt = $this->db->prepare("
            UPDATE users
            SET 
                password = :password,
                reset_token = NULL,
                reset_token_expiry = NULL
            WHERE reset_token = :token
        ");

			$updateStmt->execute([
				':password' => $newPassword,
				':token' => $token
			]);

			if ($updateStmt->rowCount() === 0) {
				return ['error' => 'Failed to update password. Please try again.'];
			}

			return ['success' => true];
		} catch (\Exception $e) {
			return ['error' => 'Password reset failed: ' . $e->getMessage()];
		}
	}
}
