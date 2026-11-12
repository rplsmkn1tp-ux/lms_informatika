<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;

echo "=== TESTING POST /logout ===\n";

function testLogoutFor($app, $user) {
    auth()->login($user);
    $session = $app['session.store'];
    $session->start();
    
    $request = Request::create('/logout', 'POST', [
        '_token' => $session->token(),
    ]);
    $request->setLaravelSession($session);
    
    return $app->handle($request);
}

$admin = User::where('role', 'admin')->first();
$response = testLogoutFor($app, $admin);
echo "Admin logout status: " . $response->getStatusCode() . " | Redirect: " . $response->headers->get('Location') . "\n";

$guru = User::where('role', 'guru')->first();
$response = testLogoutFor($app, $guru);
echo "Guru logout status: " . $response->getStatusCode() . " | Redirect: " . $response->headers->get('Location') . "\n";

$siswa = User::where('role', 'siswa')->first();
$response = testLogoutFor($app, $siswa);
echo "Siswa logout status: " . $response->getStatusCode() . " | Redirect: " . $response->headers->get('Location') . "\n";
