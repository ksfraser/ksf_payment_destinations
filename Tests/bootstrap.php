<?php
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}
// Load famock stubs (FA native defines/functions) - no deprecated base classes
// Only loaded when vendor/autoload.php is present (dev); integration uses FA's real stubs
if (file_exists(__DIR__ . '/../vendor/ksfraser/famock/php/FAMock.php')) {
    require_once __DIR__ . '/../vendor/ksfraser/famock/php/FAMock.php';
}
