<?php

namespace App\Controllers;

use App\Services\AuthService;
use App\Services\SecurityService;

class AuthController
{
	private AuthService $authService;
	private SecurityService $securityService;

	private function ensureSessionStarted(): void
	{
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}
	}

	public function __construct()
	{

		$this->ensureSessionStarted();
		$this->authService = new AuthService();
		$this->securityService = new SecurityService();
	}

	public function getCsrfToken(): void
	{
		header('Content-Type: application/json');
		echo json_encode([
			'csrf_token' => $this->securityService->generateCsrfToken()
		]);
	}
	private function getRequestData(): array
	{
		$contentType = $_SERVER['CONTENT_TYPE'] ?? '';

		if (stripos($contentType, 'application/json') !== false) {
			return json_decode(file_get_contents('php://input'), true) ?? [];
		}

		return $_POST ?: [];
	}

	private function redirectWithMessage(string $path, string $message, string $type = 'error'): void
	{
		$this->ensureSessionStarted();
		$_SESSION[$type] = $message;

		// Debugging logs
		error_log("Redirecting to: $path with message: $message");

		header("Location: $path");
		exit;
	}


	public function register(): void
	{
		$data = $this->getRequestData();
		error_log('Register data: ' . print_r($data, true));  // Debug without output
		if (!$this->securityService->checkRateLimit($_SERVER['REMOTE_ADDR'], 'register')) {
			$this->redirectWithMessage('/register', 'Too many attempts. Please try again later.');
		}


		// Check if required fields exist
		if (!isset($data['password']) || !isset($data['confirm_password'])) {
			$this->redirectWithMessage('/register', 'Password fields are required');
			return;
		}

		// Additional validation for form submission
		if ($data['password'] !== $data['confirm_password']) {
			$this->redirectWithMessage('/register', 'Passwords do not match');
			return;
		}

		$result = $this->authService->register($data);
		// var_dump($result);
		if (isset($result['error'])) {
			$this->redirectWithMessage('/register', $result['error']);
		}

		$this->redirectWithMessage('/login', 'Registration successful! Please login.', 'success');
	}

	public function login(): void
	{
		if (!$this->securityService->checkRateLimit($_SERVER['REMOTE_ADDR'], 'login')) {
			$this->redirectWithMessage('/login', 'Too many attempts. Please try again later.');
		}

		$data = $this->getRequestData();

		error_log('Login data: ' . print_r($data, true));

		$result = $this->authService->login($data);

		if (isset($result['error'])) {
			$this->redirectWithMessage('/login', $result['error'], 'error');
		}

		$_SESSION['user'] = $result['user'];

		$this->redirectWithMessage('/dashboard', 'Login successful!', 'success');
	}

	public function initiatePasswordReset(): void
	{
		if (!$this->securityService->checkRateLimit($_SERVER['REMOTE_ADDR'], 'reset_password')) {
			$this->redirectWithMessage('/reset-password', 'Too many attempts. Please try again later.');
		}

		$result = $this->authService->initiatePasswordReset($this->getRequestData());

		// In a real application, you would send an email with the reset link
		// For testing, we'll redirect with the token in the URL
		if (isset($result['debug_token'])) {
			$this->redirectWithMessage(
				"/reset-password/confirm?token={$result['debug_token']}",
				'Please check your email for the reset link.',
				'success'
			);
		}

		$this->redirectWithMessage('/reset-password', $result['error'] ?? 'Unknown error occurred');
	}

	public function resetPassword(): void
	{
		$data = $this->getRequestData();

		// Log the received data for debugging
		error_log('Reset password data: ' . print_r($data, true));

		if (empty($data['csrf_token']) || !$this->securityService->validateCsrfToken($data['csrf_token'])) {
			$this->redirectWithMessage(
				'/reset-password/confirm?token=' . ($data['token'] ?? ''),
				'Invalid CSRF token'
			);
		}

		if (!$this->securityService->checkRateLimit($_SERVER['REMOTE_ADDR'], 'reset_password_confirm')) {
			$this->redirectWithMessage('/reset-password', 'Too many attempts. Please try again later.');
		}

		if (empty($data['password']) || $data['password'] !== ($data['confirm_password'] ?? '')) {
			$this->redirectWithMessage(
				'/reset-password/confirm?token=' . ($data['token'] ?? ''),
				'Passwords do not match or are empty'
			);
		}

		$result = $this->authService->resetPassword($data);

		if (isset($result['error'])) {
			$this->redirectWithMessage(
				'/reset-password/confirm?token=' . ($data['token'] ?? ''),
				$result['error']
			);
		}

		$this->redirectWithMessage('/login', 'Password reset successfully. Please log in.', 'success');
	}

	public function logout(): void
	{
		$this->ensureSessionStarted();
		$_SESSION = [];
		session_destroy();

		$this->redirectWithMessage('/login', 'You have been logged out.', 'success');
	}
}
