<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'brh@siswa.sch')->first();
$session = $app->make('session')->driver();
$session->start();
$req = \Illuminate\Http\Request::create('/absensi', 'GET');
$req->setLaravelSession($session);
$req->setUserResolver(fn() => $user);
$app->instance('request', $req);
\Illuminate\Support\Facades\Auth::login($user);
$res = $kernel->handle($req);

echo 'Status: ' . $res->getStatusCode() . ' | Redirect: ' . $res->headers->get('Location') . ' | Content Length: ' . strlen($res->getContent()) . PHP_EOL;
if ($res->getStatusCode() === 200) {
    echo "SUCCESS: Halaman Kehadiran Saya berhasil dirender!\n";
}
