<?php
// Simple mail helper that gracefully logs to /emails/ if mail() isn't set up.
function send_email_or_log(string $to, string $subject, string $message): bool {
  $headers = "Content-Type: text/plain; charset=utf-8\r\n";
  $ok = @mail($to, $subject, $message, $headers);
  if ($ok) return true;
  $dir = __DIR__ . '/../emails';
  if (!is_dir($dir)) @mkdir($dir, 0777, true);
  $file = $dir . '/' . date('Ymd_His') . '_' . preg_replace('/[^a-z0-9]+/i', '_', $to) . '.txt';
  file_put_contents($file, "TO: $to\nSUBJECT: $subject\n\n$message\n");
  return true;
}
?>
