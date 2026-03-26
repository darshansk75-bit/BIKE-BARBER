<?php
$f = 'c:\wamp64\logs\php_error.log';
if (file_exists($f)) {
    $lines = file($f);
    echo implode("", array_slice($lines, -20));
} else {
    echo "Log file not found.\n";
}
?>
