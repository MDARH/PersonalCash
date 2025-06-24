<?php
require __DIR__."/vendor/autoload.php";
$app = require_once __DIR__."/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$reasons = DB::table("transactions")->select("reason")->whereNotNull("reason")->distinct()->get()->pluck("reason")->toArray();
echo json_encode($reasons);