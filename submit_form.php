<?php

/**
 * submit_form.php — Server-Side Form Handler
 *
 * Receives POST data from the contact form, validates it,
 * and sends a beautifully formatted HTML email to the website owner.
 *
 * Requirements:
 * • PHP 7.4+ with mail() configured, OR use PHPMailer/SMTP (recommended).
 * • Set OWNER_EMAIL to the address you want to receive messages at.
 *
 * Security measures applied:
 * • Input sanitisation with filter_var
 * • Email validation
 * • Header injection prevention
 * • Output buffering & JSON response for fetch()-based front-ends
 */

declare(strict_types=1);

// ─── Configuration ────────────────────────────────────────────────────────────

/** Email address that will receive the contact messages. */
const OWNER_EMAIL = 'barhate.komal12@gmail.com';

/** From / Reply-To name shown in the inbox. */
const SITE_NAME = 'Komal Barhate';

/** Maximum lengths to enforce server-side (mirrors front-end rules). */
const MAX_NAME_LEN    = 100;
const MAX_SUBJECT_LEN = 150;
const MAX_MSG_LEN     = 1000;

// ─── Helpers ──────────────────────────────────────────────────────────────────

function jsonResponse(bool $ok, string $message, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $ok, 'message' => $message]);
    exit;
}

function sanitiseText(string $value): string
{
    return trim(strip_tags($value));
}

// ─── Only accept POST ─────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.', 405);
}

// ─── Collect & Sanitise Inputs ────────────────────────────────────────────────

$name    = sanitiseText($_POST['name']    ?? '');
$email   = sanitiseText($_POST['email']   ?? '');
$subject = sanitiseText($_POST['subject'] ?? 'No Subject');
$message = sanitiseText($_POST['message'] ?? '');

// ─── Server-Side Validation ───────────────────────────────────────────────────

$errors = [];

if (empty($name)) {
    $errors['name'] = 'Full name is required.';
} elseif (strlen($name) < 2) {
    $errors['name'] = 'Name must be at least 2 characters.';
} elseif (strlen($name) > MAX_NAME_LEN) {
    $errors['name'] = 'Name is too long.';
} elseif (!preg_match("/^[\p{L}\s'\-]+$/u", $name)) {
    $errors['name'] = 'Name contains invalid characters.';
}

if (empty($email)) {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please provide a valid email address.';
}

if (empty($message)) {
    $errors['message'] = 'A message is required.';
} elseif (strlen($message) < 10) {
    $errors['message'] = 'Message must be at least 10 characters.';
} elseif (strlen($message) > MAX_MSG_LEN) {
    $errors['message'] = 'Message exceeds the 1000-character limit.';
}

if (!empty($errors)) {
    http_response_code(422);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'errors' => $errors]);
    exit;
}

// ─── Prevent Header Injection ─────────────────────────────────────────────────

$safeName    = preg_replace('/[\r\n]+/', ' ', $name);
$safeEmail   = preg_replace('/[\r\n]+/', '', $email);
$safeSubject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
// Preserve line breaks in the message body
$safeMessageFormatted = nl2br($safeMessage);

// ─── Timestamp ────────────────────────────────────────────────────────────────

$timestamp   = date('D, d M Y \a\t H:i:s T');
$senderIP    = htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
$initials    = strtoupper(substr($safeName, 0, 1));

// ─── Build HTML Email ─────────────────────────────────────────────────────────

$htmlBody = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>New Message — {$safeSubject}</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Segoe UI',Helvetica,Arial,sans-serif;">

  <!-- Outer wrapper -->
  <table width="100%" cellpadding="0" cellspacing="0" border="0"
         style="background-color:#f0f4f8;padding:40px 16px;">
    <tr>
      <td align="center">

        <!-- Card -->
        <table width="600" cellpadding="0" cellspacing="0" border="0"
               style="max-width:600px;width:100%;background:#ffffff;
                      border-radius:16px;overflow:hidden;
                      box-shadow:0 4px 24px rgba(0,0,0,0.08);">

          <!-- ── Header ── -->
          <tr>
            <td style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);
                       padding:40px 40px 32px;text-align:center;">
              <!-- Logo mark -->
              <div style="display:inline-block;width:48px;height:48px;
                          background:rgba(255,255,255,0.12);border-radius:12px;
                          line-height:48px;font-size:22px;margin-bottom:16px;">
                ✉️
              </div>
              <h1 style="margin:0;color:#ffffff;font-size:22px;font-weight:700;
                         letter-spacing:-0.3px;">
                New Contact Message
              </h1>
              <p style="margin:6px 0 0;color:rgba(255,255,255,0.6);font-size:13px;">
                Submitted via your portfolio contact form
              </p>
            </td>
          </tr>

          <!-- ── Subject Banner ── -->
          <tr>
            <td style="background:#e8f0fe;padding:14px 40px;border-bottom:1px solid #dce8fd;">
              <p style="margin:0;font-size:13px;color:#5c6bc0;font-weight:600;
                        text-transform:uppercase;letter-spacing:0.8px;">Subject</p>
              <p style="margin:4px 0 0;font-size:18px;color:#1a237e;font-weight:700;">
                {$safeSubject}
              </p>
            </td>
          </tr>

          <!-- ── Sender Details ── -->
          <tr>
            <td style="padding:32px 40px 0;">
              <p style="margin:0 0 16px;font-size:13px;color:#9e9e9e;
                        font-weight:600;text-transform:uppercase;letter-spacing:0.8px;">
                Sender Details
              </p>

              <!-- Sender card -->
              <table width="100%" cellpadding="0" cellspacing="0" border="0"
                     style="background:#f8faff;border-radius:12px;
                            border:1px solid #e3eafc;overflow:hidden;">
                <tr>
                  <td style="padding:20px 24px;">
                    <table cellpadding="0" cellspacing="0" border="0">
                      <tr>
                        <!-- Avatar -->
                        <td style="vertical-align:top;padding-right:16px;">
                          <div style="width:48px;height:48px;border-radius:50%;
                                      background:linear-gradient(135deg,#667eea,#764ba2);
                                      text-align:center;line-height:48px;
                                      font-size:20px;font-weight:700;color:#fff;">
                            {$initials}
                          </div>
                        </td>
                        <!-- Info -->
                        <td style="vertical-align:top;">
                          <p style="margin:0;font-size:17px;font-weight:700;color:#1a1a2e;">
                            {$safeName}
                          </p>
                          <a href="mailto:{$safeEmail}"
                             style="color:#4f6ef2;font-size:14px;text-decoration:none;">
                            {$safeEmail}
                          </a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- ── Message Body ── -->
          <tr>
            <td style="padding:28px 40px 0;">
              <p style="margin:0 0 12px;font-size:13px;color:#9e9e9e;
                        font-weight:600;text-transform:uppercase;letter-spacing:0.8px;">
                Message
              </p>
              <div style="background:#f8f9fa;border-left:4px solid #4f6ef2;
                          border-radius:0 12px 12px 0;padding:20px 24px;
                          font-size:15px;line-height:1.75;color:#37474f;">
                {$safeMessageFormatted}
              </div>
            </td>
          </tr>

          <!-- ── Reply CTA ── -->
          <tr>
            <td style="padding:28px 40px 0;text-align:center;">
              <a href="mailto:{$safeEmail}?subject=Re: {$safeSubject}"
                 style="display:inline-block;background:linear-gradient(135deg,#4f6ef2,#764ba2);
                        color:#ffffff;font-size:15px;font-weight:600;
                        text-decoration:none;padding:14px 36px;
                        border-radius:50px;letter-spacing:0.3px;">
                ↩ Reply to {$safeName}
              </a>
            </td>
          </tr>

          <!-- ── Meta Info ── -->
          <tr>
            <td style="padding:28px 40px;">
              <table width="100%" cellpadding="0" cellspacing="0" border="0"
                     style="border-top:1px solid #eee;padding-top:20px;">
                <tr>
                  <td style="font-size:12px;color:#bdbdbd;line-height:1.8;">
                    🕐 &nbsp;<strong>Sent:</strong> {$timestamp}<br/>
                    🌐 &nbsp;<strong>IP Address:</strong> {$senderIP}
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- ── Footer ── -->
          <tr>
            <td style="background:#1a1a2e;padding:20px 40px;text-align:center;">
              <p style="margin:0;font-size:12px;color:rgba(255,255,255,0.4);">
                This email was sent automatically from
                <strong style="color:rgba(255,255,255,0.6);">Komal Barhate's Portfolio</strong>
                — do not reply directly to this notification.
              </p>
            </td>
          </tr>

        </table>
        <!-- /Card -->

      </td>
    </tr>
  </table>

</body>
</html>
HTML;

// ─── Plain-text fallback (for email clients that don't render HTML) ────────────

$plainFallback = <<<EOT
New Contact Message
===================

Subject : {$subject}
Name    : {$safeName}
Email   : {$safeEmail}
Sent at : {$timestamp}
IP      : {$senderIP}

---
Message:
{$message}
---

Reply to: {$safeEmail}
EOT;

// ─── Build Multipart MIME Email ───────────────────────────────────────────────

$boundary   = 'boundary_' . md5(uniqid((string)mt_rand(), true));
$emailSubject = "[ContactHub] {$subject}";

$headers  = "From: {$safeName} <{$safeEmail}>\r\n";
$headers .= "Reply-To: {$safeEmail}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$body  = "--{$boundary}\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
$body .= quoted_printable_encode($plainFallback) . "\r\n\r\n";

$body .= "--{$boundary}\r\n";
$body .= "Content-Type: text/html; charset=UTF-8\r\n";
$body .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
$body .= quoted_printable_encode($htmlBody) . "\r\n\r\n";

$body .= "--{$boundary}--";

// ─── Send ─────────────────────────────────────────────────────────────────────

$sent = mail(OWNER_EMAIL, $emailSubject, $body, $headers);

// ─── Response ─────────────────────────────────────────────────────────────────

if ($sent) {
    jsonResponse(true, "Your message has been sent. We'll be in touch soon!");
} else {
    error_log("[ContactHub] mail() failed. Name={$safeName} Email={$safeEmail}");
    jsonResponse(false, 'We could not send your message right now. Please try again later.', 500);
}
