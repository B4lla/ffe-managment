<?php
require __DIR__ . '/vendor/autoload.php';
$u = new App\Models\User();
$u->name = 'test';
var_export($u->getAttributes());
