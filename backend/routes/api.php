<?php
return [
	'GET /api/auth/csrf-token' => ['AuthController', 'getCsrfToken'],
	'POST /api/auth/register' => ['AuthController', 'register'],
	'POST /api/auth/login' => ['AuthController', 'login'],
	'POST /api/auth/reset-password/request' => ['AuthController', 'initiatePasswordReset'],
	'POST /api/auth/reset-password/confirm' => ['AuthController', 'resetPassword']
];
