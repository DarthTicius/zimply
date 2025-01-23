<?php

namespace App\Services;

use App\Config\Database;
use PDO;

class AuthService
{
	private PDO $db;

	public function __construct()
	{
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

			if (!$user) {
				return ['error' => 'Invalid credentials'];
			}

			if (!password_verify($data['password'], $user['password'])) {
				return ['error' => 'Invalid credentials'];
			}
			if ($user) {
				$_SESSION['user_id'] = $user['id'];
				$_SESSION['email'] = $user['email'];

				return [
					'message' => 'Login successful',
					'user' => [
						'id' => $user['id'],
						'email' => $user['email']
					]
				];
			}

			return ['error' => 'Invalid credentials'];
		} catch (\Exception $e) {
			return ['error' => 'Login failed'];
		}
	}

	public function initiatePasswordReset(array $data): array
	{
		try {
			$stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
			$stmt->execute([$data['email']]);
			if (!$stmt->fetch()) {
				return ['message' => 'If the email exists, a reset link will be sent'];
			}

			$resetToken = bin2hex(random_bytes(32));
			$resetTokenExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

			$stmt = $this->db->prepare("
                UPDATE users 
                SET reset_token = ?, reset_token_expiry = ? 
                WHERE email = ?
            ");
			$stmt->execute([$resetToken, $resetTokenExpiry, $data['email']]);

			return ['message' => 'Password reset instructions sent', 'debug_token' => $resetToken];
		} catch (\Exception $e) {
			return ['error' => 'Password reset failed'];
		}
	}

	public function resetPassword(array $data): array
	{
		if (empty($data['token'])) {
			return ['error' => 'Reset token is required'];
		}

		if (strlen($data['password']) < 8) {
			return ['error' => 'Password must be at least 8 characters'];
		}

		try {
			$stmt = $this->db->prepare("
				SELECT id, email, reset_token_expiry 
				FROM users 
				WHERE reset_token = ? 
				AND reset_token_expiry > CURRENT_TIMESTAMP
			");
			$stmt->execute([$data['token']]);
			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			// Log the token and user data for debugging
			error_log('Reset token: ' . $data['token']);
			error_log('User data: ' . print_r($user, true));

			if (!$user) {
				error_log('Invalid or expired reset token: ' . $data['token']); // Debugging log
				return ['error' => 'Invalid or expired reset token'];
			}

			$hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

			$stmt = $this->db->prepare("
				UPDATE users 
				SET password = ?, reset_token = NULL, reset_token_expiry = NULL
				WHERE id = ? AND reset_token = ?
			");
			$stmt->execute([$hashedPassword, $user['id'], $data['token']]);

			return ['message' => 'Password updated successfully'];
		} catch (\Exception $e) {
			error_log('Password reset failed: ' . $e->getMessage()); // Debugging log
			return ['error' => 'Password reset failed'];
		}
	}
}
