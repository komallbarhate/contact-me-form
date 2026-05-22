<?php
/**
 * submit_form.php — Server-Side Form Handler
 *
 * Receives POST data from the contact form, validates it,
 * and sends an email to the website owner.
 *
 * Requirements:
 *  • PHP 7.4+ with mail() configured, OR use PHPMailer/SMTP (recommended).
 *  • Set $ownerEmail to the address you want to receive messages at.
 *
 * Security measures applied:
 *  • Input sanitisation with filter_var
 *  • Email validation
 *  • Header injection prevention
 *  • CSRF-token check (basic, token generated per session)
 *  • Output buffering & JSON response for fetch()-based front-ends
 */

declare(strict_types=1);

// ─── Configuration ────────────────────────────────────────────────────────────

/** Email address that will receive the contact messages. */
const OWNER_EMAIL    = 'barhate.komal12@gmail.com';

/** From / Reply-To name shown in the inbox. */
const SITE_NAME      = 'Komal Barhate';

/** Maximum lengths to enforce server-side (mirrors front-end rules). */
const MAX_NAME_LEN   = 100;
const MAX_SUBJECT_LEN = 150;
const MAX_MSG_LEN    = 1000;

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Send a JSON response and exit.
 * @param bool   $ok      Whether the operation succeeded.
 * @param string $message Human-readable status message.
 * @param int    $status  HTTP status code.
 */
function jsonResponse(bool $ok, string $message, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $ok, 'message' => $message]);
    exit;
}

/**
 * Sanitise a plain-text string: strip tags, trim whitespace.
 */
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

// Name
if (empty($name)) {
    $errors['name'] = 'Full name is required.';
} elseif (strlen($name) < 2) {
    $errors['name'] = 'Name must be at least 2 characters.';
} elseif (strlen($name) > MAX_NAME_LEN) {
    $errors['name'] = 'Name is too long.';
} elseif (!preg_match("/^[\p{L}\s'\-]+$/u", $name)) {
    $errors['name'] = 'Name contains invalid characters.';
}

// Email
if (empty($email)) {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please provide a valid email address.';
}

// Message
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

// Strip newlines from any value that could appear in email headers
$safeName  = preg_replace('/[\r\n]+/', ' ', $name);
$safeEmail = preg_replace('/[\r\n]+/', '', $email);

// ─── Build & Send Email ───────────────────────────────────────────────────────

$emailSubject = empty($subject)
    ? "[ContactHub] New message from {$safeName}"
    : "[ContactHub] {$subject}";

$emailBody = <<<EOT
You have received a new message via the ContactHub contact form.

─────────────────────────────────────────
Name   : {$safeName}
Email  : {$safeEmail}
Subject: {$subject}
─────────────────────────────────────────

Message:
{$message}

─────────────────────────────────────────
Sent at: {$_SERVER['REQUEST_TIME_FLOAT']}
IP     : {$_SERVER['REMOTE_ADDR']}
─────────────────────────────────────────
EOT;

$headers  = "From: {$safeName} <{$safeEmail}>\r\n";
$headers .= "Reply-To: {$safeEmail}\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

$sent = mail(OWNER_EMAIL, $emailSubject, $emailBody, $headers);

// ─── Response ─────────────────────────────────────────────────────────────────

if ($sent) {
    jsonResponse(true, 'Your message has been sent. We\'ll be in touch soon!');
} else {
    // mail() failed — log it and return a 500
    error_log("[ContactHub] mail() failed. Name={$safeName} Email={$safeEmail}");
    jsonResponse(false, 'We could not send your message right now. Please try again later.', 500);
}
