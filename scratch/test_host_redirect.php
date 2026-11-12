<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Http\Request;

$admin = User::where('role', 'admin')->first();
auth()->login($admin);
$session = $app['session.store'];
$session->start();

$request = Request::create('http://127.0.0.1:8000/logout', 'POST', [
    '_token' => $session->token(),
]);
$request->headers->set('host', '127.0.0.1:8000');
$request->setLaravelSession($session);

$response = $app->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Location: " . $response->headers->get('Location') . "\n";

