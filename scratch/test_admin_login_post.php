<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;

echo "=== TESTING POST /login WITH CSRF TOKEN ===\n";

// Start session
$session = $app->make('session')->driver();
$session->start();
$token = $session->token();

$admin = User::where('role', 'admin')->first();

$loginRequest = Request::create('/login', 'POST', [
    'login_id' => $admin->email,
    'password' => 'password',
    '_token' => $token,
]);

$loginRequest->setLaravelSession($session);
$loginRequest->cookies->set($session->getName(), $session->getId());

$response = $kernel->handle($loginRequest);

echo "Status Code: " . $response->getStatusCode() . "\n";
echo "Redirect URL: " . $response->headers->get('Location') . "\n";

if ($response->getStatusCode() === 302 && str_contains($response->headers->get('Location'), 'dashboard')) {
    echo "SUCCESS: Login credentials validated and redirected to /dashboard!\n";
} else {
    echo "Check response content:\n" . substr($response->getContent(), 0, 500) . "\n";
}

