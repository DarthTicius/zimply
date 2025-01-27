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
		date_default_timezone_set('Europe/Berlin'); // Ensure the time zone is set correctly
		$this->ensureSessionStarted();
		$this->authService = new AuthService();
		$this->securityService = new SecurityService();
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

		header("Location: $path");
		exit;
	}

	public function register(): void
	{
		$data = $this->getRequestData();
		if (!$this->securityService->checkRateLimit($_SERVER['REMOTE_ADDR'], 'register')) {
			$this->redirectWithMessage('/register', 'Too many attempts. Please try again later.');
		}

		if (!isset($data['password']) || !isset($data['confirm_password'])) {
			$this->redirectWithMessage('/register', 'Password fields are required');
			return;
		}

		if ($data['password'] !== $data['confirm_password']) {
			$this->redirectWithMessage('/register', 'Passwords do not match');
			return;
		}

		$result = $this->authService->register($data);
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

		// Simulate email sending by redirecting with the token in the URL
		if (isset($result['debug_token'])) {
			$encodedToken = urlencode($result['debug_token']);
			$this->redirectWithMessage(
				"/reset-password/confirm?token={$encodedToken}",
				'Please check your email for the reset link.',
				'success'
			);
		}

		$this->redirectWithMessage('/reset-password', $result['error'] ?? 'Unknown error occurred');
	}

	public function resetPassword(): void
	{
		$data = $this->getRequestData();

		if (empty($data['csrf_token']) || !$this->securityService->validateCsrfToken($data['csrf_token'])) {
			$this->redirectWithMessage(
				'/reset-password/confirm?token=' . urlencode(trim(urldecode($data['token'] ?? ''))),
				'Invalid CSRF token'
			);
		}

		if (!$this->securityService->checkRateLimit($_SERVER['REMOTE_ADDR'], 'reset_password_confirm')) {
			$this->redirectWithMessage('/reset-password', 'Too many attempts. Please try again later.');
		}

		if (empty($data['password']) || $data['password'] !== ($data['confirm_password'] ?? '')) {
			$this->redirectWithMessage(
				'/reset-password/confirm?token=' . urlencode(trim(urldecode($data['token'] ?? ''))),
				'Passwords do not match or are empty'
			);
		}

		$result = $this->authService->resetPassword($data);

		if (isset($result['error'])) {
			$this->redirectWithMessage(
				'/reset-password/confirm?token=' . urlencode(trim(urldecode($data['token'] ?? ''))),
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

		$this->ensureSessionStarted();
		$_SESSION['success'] = 'You have been logged out successfully.';

		header('Content-Type: application/json');
		echo json_encode(['message' => 'Logged out successfully']);
		exit;
	}
}
