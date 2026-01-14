<?php
$traceFile = __DIR__ . '/debug_trace.txt';
if (file_exists($traceFile)) {
    echo nl2br(htmlspecialchars(file_get_contents($traceFile)));
} else {
    echo "Trace file not found.";
}
?>
