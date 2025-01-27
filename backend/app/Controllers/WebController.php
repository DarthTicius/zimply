<?php

namespace App\Controllers;

use App\Services\SecurityService;

class WebController
{
	private SecurityService $security;

	public function __construct()
	{
		$this->security = new SecurityService();
	}

	private function render(string $view, array $data = []): void
	{
		$content = $this->renderView($view, $data);
		include __DIR__ . '/../Views/layout.php';
	}

	private function renderView(string $view, array $data): string
	{
		extract($data);
		ob_start();
		include __DIR__ . "/../Views/{$view}.php";
		return ob_get_clean();
	}

	public function login(): void
	{
		$this->render('login', [
			'title' => 'Login',
			'csrf_token' => $this->security->generateCsrfToken()
		]);
	}

	public function register(): void
	{
		$this->render('register', [
			'title' => 'Register',
			'csrf_token' => $this->security->generateCsrfToken()
		]);
	}

	public function resetPasswordRequest(): void
	{
		$this->render('reset-password-request', [
			'title' => 'Reset Password',
			'csrf_token' => $this->security->generateCsrfToken()
		]);
	}

	public function resetPasswordConfirm(): void
	{
		$token = $_GET['token'] ?? '';
		if (empty($token)) {
			$_SESSION['error'] = 'Invalid reset token';
			header('Location: /reset-password');
			exit;
		}

		$this->render('reset-password-confirm', [
			'title' => 'Set New Password',
			'csrf_token' => $this->security->generateCsrfToken(),
			'reset_token' => $token
		]);
	}

	public function dashboard(): void
	{
		$this->render('dashboard', [
			'title' => 'Dashboard'
		]);
	}
}
