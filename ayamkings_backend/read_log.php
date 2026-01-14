<?php
$logFile = __DIR__ . '/php_error_log.txt';
if (file_exists($logFile)) {
    echo nl2br(htmlspecialchars(file_get_contents($logFile)));
} else {
    echo "Log file not found.";
}
?>
