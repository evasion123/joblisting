<?php
// lib/email.php — PHPMailer + optional /emails fallback
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function env_bool($key, $default=false) {
  $v = getenv($key);
  if ($v === false) return $default;
  $v = strtolower(trim($v));
  return in_array($v, ['1','true','yes','on'], true);
}

function send_email_or_log(string $to, string $subject, string $message, ?string $replyTo=null): bool {
  // Basic subject hardening
  $subject = preg_replace("/[\r\n]+/", " ", $subject);

  $mail = new PHPMailer(true);
  try {
    // SMTP config from .env
    $mail->isSMTP();
    $mail->Host       = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
    $mail->Port       = (int)(getenv('SMTP_PORT') ?: 587);
    $mail->SMTPAuth   = env_bool('SMTP_AUTH', true);
    $mail->Username   = getenv('SMTP_USER') ?: 'o32887976@gmail.com';
    $mail->Password   = getenv('SMTP_PASS') ?: 'quwhshiolgbbbyhj';
    $secure           = strtolower(getenv('SMTP_SECURE') ?: '');
    if ($secure === 'tls')      { $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; }
    elseif ($secure === 'ssl')  { $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; }
    // else: no encryption

    $mail->SMTPDebug  = (int)(getenv('SMTP_DEBUG') ?: 0); // 0=off

    $mail->CharSet = 'UTF-8';

    // From / Reply-To
    $from     = getenv('MAIL_FROM') ?: 'o32887976@gmail.com';
    $fromName = getenv('MAIL_FROM_NAME') ?: 'Jobs Portal';
    $mail->setFrom($from, $fromName);
    if ($replyTo) $mail->addReplyTo($replyTo);

    // To
    $mail->addAddress($to);

    // Body (plain text; switch to HTML if you like)
    $mail->Subject = $subject;
    $mail->Body    = $message;
    $mail->AltBody = $message;  // keep same for plain text fallback
    // If you want HTML: uncomment the two lines below and send HTML in $message
    // $mail->isHTML(true);
    // $mail->AltBody = strip_tags($message);

    $mail->send();
    return true;

  } catch (\Throwable $e) {
    // Optional dev fallback: write a file to /emails so you can see the message
    if (env_bool('MAIL_LOG_FALLBACK', true)) {
      $dir = __DIR__ . '/../emails';
      if (!is_dir($dir)) @mkdir($dir, 0777, true);
      $file = $dir . '/' . date('Ymd_His') . '_' . preg_replace('/[^a-z0-9]+/i', '_', $to) . '.txt';
      @file_put_contents($file,
        "TO: $to\nSUBJECT: $subject\nREPLY-TO: ".($replyTo ?: 'n/a')."\n\n$message\n\nERROR: ".$e->getMessage()."\n"
      );
      return true; // return false if you want the caller to show an error
    }
    return false;
  }
}
