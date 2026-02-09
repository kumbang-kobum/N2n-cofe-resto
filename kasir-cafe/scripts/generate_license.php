<?php

if ($argc < 3) {
    echo "Usage: php scripts/generate_license.php <installation_code> <master_key>\n";
    exit(1);
}

$installCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $argv[1]));
$masterKey = $argv[2];

$raw = hash_hmac('sha256', $installCode, $masterKey);
$license = strtoupper(substr($raw, 0, 32));

echo $license . "\n";
