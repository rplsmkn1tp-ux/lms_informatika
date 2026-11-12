<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
// just check if function exists
echo "clean exists? " . (function_exists('clean') ? 'Yes' : 'No');
