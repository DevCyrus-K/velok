<?php
declare(strict_types=1);

require_once __DIR__ . '/email-helper.php';

kwikshift_require_post();

$sourcePage = $_POST['source_page'] ?? '';

if (kwikshift_env_bool('FORM_CSRF_PROTECTION_ENABLED', true) && !kwikshift_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    kwikshift_log_form_event('contact', 'rejected', 'Invalid CSRF token for contact form.', [], (string) $sourcePage);
    kwikshift_json_response(false, 'Your secure session expired. Please refresh the page and try again.', 419);
}

$requestContext = kwikshift_submission_context((string) $sourcePage);

if (
    kwikshift_env_bool('FORM_RATE_LIMITING_ENABLED', true) &&
    kwikshift_is_rate_limited(
        'contact',
        $requestContext['ip_address'],
        kwikshift_env_int('CONTACT_FORM_RATE_LIMIT_MAX_ATTEMPTS', 5, 1, 100),
        kwikshift_env_int('CONTACT_FORM_RATE_LIMIT_WINDOW_SECONDS', 900, 60, 86400)
    )
) {
    kwikshift_log_form_event('contact', 'rate_limited', 'Contact form rate limit exceeded.', [], (string) $sourcePage);
    kwikshift_json_response(false, 'Too many contact requests were sent from your connection. Please wait a few minutes and try again.', 429);
}

// ===== VALIDATION PHASE =====
$firstName = kwikshift_clean_text((string) ($_POST['firstname'] ?? ''), 80);
$lastName = kwikshift_clean_text((string) ($_POST['lastname'] ?? ''), 80);
$email = kwikshift_clean_email((string) ($_POST['email'] ?? ''));
$phone = kwikshift_clean_phone((string) ($_POST['phone'] ?? ''), 32);
$message = kwikshift_clean_textarea((string) ($_POST['message'] ?? ''), 3000);
$subject = kwikshift_clean_text((string) ($_POST['subject'] ?? 'Website Contact Inquiry'), 160);

$errors = [];

// Validate each field
if ($firstName === '') {
    $errors['firstname'] = 'Please enter your first name.';
}

if ($lastName === '') {
    $errors['lastname'] = 'Please enter your last name.';
}

if ($email === '') {
    $errors['email'] = 'Please enter your email address.';
} elseif (!kwikshift_validate_email($email)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if ($phone === '') {
    $errors['phone'] = 'Please enter your phone number.';
} elseif (!kwikshift_validate_phone($phone)) {
    $errors['phone'] = 'Please enter a valid phone number so our team can reach you quickly.';
}

if ($message === '') {
    $errors['message'] = 'Please tell us about your move or request.';
} elseif (mb_strlen($message) < 10) {
    $errors['message'] = 'Please share a little more detail in your message.';
}

// Return validation errors if any
if ($errors !== []) {
    kwikshift_log_form_event('contact', 'validation_failed', 'Contact form validation failed.', [
        'errors' => implode(', ', array_keys($errors)),
        'email' => $email,
    ], (string) $sourcePage);
    kwikshift_json_validation_response('Please review the highlighted contact details and try again.', $errors, 422);
}

// ===== DATABASE INSERTION PHASE =====
$fullName = trim($firstName . ' ' . $lastName);
$submissionId = null;

try {
    $submissionId = kwikshift_db_insert('contact_form_submissions', [
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'subject' => $subject,
        'message' => $message,
        'source_page' => $requestContext['source_page'],
        'ip_address' => $requestContext['ip_address'],
        'user_agent' => $requestContext['user_agent'],
        'status' => 'new',
    ]);
} catch (Throwable $exception) {
    kwikshift_log_form_event('contact', 'db_failed', 'Failed to store contact submission in database.', [
        'exception_message' => substr($exception->getMessage(), 0, 200),
        'email' => $email,
    ], (string) $sourcePage);
    
    kwikshift_write_log('forms', 'error', 'contact_store_failed', 'Contact submission could not be stored.', [
        'exception' => $exception->getMessage(),
        'email' => $email,
        'source_page' => $requestContext['source_page'],
    ]);
    
    kwikshift_json_response(false, 'We could not process your request right now. Please try again in a moment.', 500);
}

if (!$submissionId || $submissionId < 1) {
    kwikshift_log_form_event('contact', 'db_failed', 'Database insert returned invalid submission ID.', [
        'submission_id' => $submissionId ?? 'null',
        'email' => $email,
    ], (string) $sourcePage);
    
    kwikshift_json_response(false, 'We could not process your request right now. Please try again in a moment.', 500);
}

// ===== EMAIL SENDING PHASE =====
$config = kwikshift_email_config();

$adminFields = [
    'Submission ID' => (string) $submissionId,
    'Timestamp' => date('Y-m-d H:i:s'),
    'Name' => $fullName,
    'Email' => $email,
    'Phone' => $phone,
    'Subject' => $subject,
    'Message' => $message,
    'Source Page' => $requestContext['source_page'],
    'IP Address' => $requestContext['ip_address'],
];

$adminResult = kwikshift_send_email([
    'to' => $config['admin_email'],
    'subject' => 'New website contact inquiry from ' . $fullName,
    'text' => kwikshift_render_admin_text('New website contact inquiry', $adminFields),
    'html' => kwikshift_render_admin_html('New website contact inquiry', $adminFields),
    'reply_to_email' => $email,
    'reply_to_name' => $fullName,
    'form_type' => 'contact',
    'direction' => 'admin',
    'submission_table' => 'contact_form_submissions',
    'submission_id' => $submissionId,
]);

$customerEmail = kwikshift_prepare_customer_email([
    'customer_name' => $firstName,
    'subject' => 'We received your message | Kwikshift Movers',
    'preview_text' => 'Your contact request is with our team and we will follow up shortly.',
    'heading' => 'Thank You for Contacting Us',
    'subheading' => 'Your message has been received and our team is reviewing it now.',
    'paragraph_one' => 'Thank you for contacting Kwikshift Movers. We have successfully received your message and our team is reviewing your moving request now.',
    'paragraph_two' => 'One of our relocation specialists will contact you shortly to answer your questions and guide you on the next best step for your move.',
    'closing_name' => 'KWIKSHIFT MOVERS Team',
]);

$clientResult = kwikshift_email_transport_unavailable($adminResult)
    ? kwikshift_email_skipped_result('Customer confirmation email skipped because the SMTP transport is currently unavailable.')
    : kwikshift_send_email([
        'to' => $email,
        'subject' => $customerEmail['subject'],
        'text' => $customerEmail['text'],
        'html' => $customerEmail['html'],
        'inline_attachments' => $customerEmail['inline_attachments'],
        'reply_to_email' => $config['contact_email'],
        'reply_to_name' => $config['sender_name'],
        'auto_submitted' => $customerEmail['auto_submitted'],
        'form_type' => 'contact',
        'direction' => 'client',
        'submission_table' => 'contact_form_submissions',
        'submission_id' => $submissionId,
    ]);

// ===== STATUS UPDATE PHASE =====
$emailStatus = ($adminResult['ok'] && $clientResult['ok']) ? 'emailed' : 'email_failed';

try {
    kwikshift_db_update_by_id('contact_form_submissions', $submissionId, [
        'status' => $emailStatus,
    ]);
} catch (Throwable $exception) {
    kwikshift_write_log('forms', 'error', 'contact_status_update_failed', 'Could not update submission status after email send.', [
        'submission_id' => $submissionId,
        'intended_status' => $emailStatus,
        'exception' => $exception->getMessage(),
    ]);
}

// ===== LOGGING PHASE =====
kwikshift_log_form_event(
    'contact',
    $emailStatus,
    $emailStatus === 'emailed'
        ? 'Contact submission stored and notification emails processed successfully.'
        : 'Contact submission stored but one or more emails failed to send.',
    [
        'submission_id' => $submissionId,
        'admin_email_sent' => $adminResult['ok'] ? 'yes' : 'no',
        'client_email_sent' => $clientResult['ok'] ? 'yes' : 'no',
        'admin_email_error' => !$adminResult['ok'] ? ($adminResult['message'] ?? 'Unknown error') : null,
        'client_email_error' => !$clientResult['ok'] ? ($clientResult['message'] ?? 'Unknown error') : null,
    ],
    (string) $sourcePage,
    'contact_form_submissions',
    $submissionId
);

// ===== RESPONSE PHASE =====
kwikshift_json_response(true, 'Thank you! We have received your message and our team will contact you shortly.', 200, [
    'submission_id' => $submissionId,
    'email_status' => $emailStatus,
]);
