<?php
declare(strict_types=1);

require_once __DIR__ . '/email-helper.php';

kwikshift_require_post();

$sourcePage = $_POST['source_page'] ?? '';

if (kwikshift_env_bool('FORM_CSRF_PROTECTION_ENABLED', true) && !kwikshift_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    kwikshift_log_form_event('quote', 'rejected', 'Invalid CSRF token for quote form.', [], (string) $sourcePage);
    kwikshift_json_response(false, 'Your secure session expired. Please refresh the page and try again.', 419);
}

$requestContext = kwikshift_submission_context((string) $sourcePage);

if (
    kwikshift_env_bool('FORM_RATE_LIMITING_ENABLED', true) &&
    kwikshift_is_rate_limited(
        'quote',
        $requestContext['ip_address'],
        kwikshift_env_int('QUOTE_FORM_RATE_LIMIT_MAX_ATTEMPTS', 6, 1, 100),
        kwikshift_env_int('QUOTE_FORM_RATE_LIMIT_WINDOW_SECONDS', 900, 60, 86400)
    )
) {
    kwikshift_log_form_event('quote', 'rate_limited', 'Quote form rate limit exceeded.', [], (string) $sourcePage);
    kwikshift_json_response(false, 'Too many quote requests were sent from your connection. Please wait a few minutes and try again.', 429);
}

// ===== VALIDATION PHASE =====
$fullName = kwikshift_clean_text((string) ($_POST['q-full-name'] ?? ''), 120);
$email = kwikshift_clean_email((string) ($_POST['q-email'] ?? ''));
$phone = kwikshift_clean_phone((string) ($_POST['q-phone'] ?? ''), 32);
$movingFrom = kwikshift_clean_text((string) ($_POST['q-depature'] ?? ''), 190);
$movingTo = kwikshift_clean_text((string) ($_POST['q-destination'] ?? ''), 190);
$moveSize = kwikshift_clean_text((string) ($_POST['q-weight'] ?? ''), 160);
$serviceType = kwikshift_clean_text((string) ($_POST['q-freight-type'] ?? ''), 120);
$additionalNotes = kwikshift_clean_textarea((string) ($_POST['q-message'] ?? ''), 4000);
$moveDate = kwikshift_normalize_date($_POST['q-move-date'] ?? null);

$allowedServiceTypes = [
    'Residential Relocation',
    'Office Relocation',
    'Long-Distance Move',
    'Packing & Storage',
    'Packing and Storage',
];
$errors = [];

if ($fullName === '') {
    $errors['q-full-name'] = 'Please enter your full name.';
}

if ($email === '') {
    $errors['q-email'] = 'Please enter your email address.';
} elseif (!kwikshift_validate_email($email)) {
    $errors['q-email'] = 'Please enter a valid email address.';
}

if ($phone === '') {
    $errors['q-phone'] = 'Please enter your phone number.';
} elseif (!kwikshift_validate_phone($phone)) {
    $errors['q-phone'] = 'Please enter a valid phone number so we can reach you quickly.';
}

if ($movingFrom === '') {
    $errors['q-depature'] = 'Please enter your pickup location.';
}

if ($movingTo === '') {
    $errors['q-destination'] = 'Please enter your destination.';
}

if ($moveSize === '') {
    $errors['q-weight'] = 'Please tell us the house size or office items involved.';
}

if ($serviceType === '') {
    $errors['q-freight-type'] = 'Please choose your move type.';
} elseif (!in_array($serviceType, $allowedServiceTypes, true)) {
    $errors['q-freight-type'] = 'Please choose a valid move type for your request.';
}

if ($additionalNotes === '') {
    $errors['q-message'] = 'Please share a few details about your move.';
} elseif (mb_strlen($additionalNotes) < 10) {
    $errors['q-message'] = 'Please share a few more details about your move so we can prepare the right quote.';
}

if ($errors !== []) {
    kwikshift_log_form_event('quote', 'validation_failed', 'Quote form validation failed.', [
        'errors' => implode(', ', array_keys($errors)),
        'email' => $email,
    ], (string) $sourcePage);
    kwikshift_json_validation_response('Please review the highlighted moving details and try again.', $errors, 422);
}

// ===== DATABASE INSERTION PHASE =====
$submissionId = null;

try {
    $submissionId = kwikshift_db_insert('quote_requests', [
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'moving_from' => $movingFrom,
        'moving_to' => $movingTo,
        'move_date' => $moveDate,
        'service_type' => $serviceType,
        'move_size' => $moveSize,
        'additional_notes' => $additionalNotes,
        'source_page' => $requestContext['source_page'],
        'ip_address' => $requestContext['ip_address'],
        'user_agent' => $requestContext['user_agent'],
        'status' => 'new',
    ]);
} catch (Throwable $exception) {
    kwikshift_log_form_event('quote', 'db_failed', 'Failed to store quote request in database.', [
        'exception_message' => substr($exception->getMessage(), 0, 200),
        'email' => $email,
    ], (string) $sourcePage);
    
    kwikshift_write_log('forms', 'error', 'quote_store_failed', 'Quote request could not be stored.', [
        'exception' => $exception->getMessage(),
        'email' => $email,
        'source_page' => $requestContext['source_page'],
    ]);
    
    kwikshift_json_response(false, 'We could not process your request right now. Please try again in a moment.', 500);
}

if (!$submissionId || $submissionId < 1) {
    kwikshift_log_form_event('quote', 'db_failed', 'Database insert returned invalid submission ID.', [
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
    'Pickup Location' => $movingFrom,
    'Destination' => $movingTo,
    'Move Size / Office Items' => $moveSize,
    'Move Type' => $serviceType,
    'Preferred Move Date' => $moveDate ?? 'Not provided',
    'Additional Notes' => $additionalNotes,
    'Source Page' => $requestContext['source_page'],
    'IP Address' => $requestContext['ip_address'],
];

$adminResult = kwikshift_send_email([
    'to' => $config['admin_email'],
    'subject' => 'New moving quote request from ' . $fullName,
    'text' => kwikshift_render_admin_text('New moving quote request', $adminFields),
    'html' => kwikshift_render_admin_html('New moving quote request', $adminFields),
    'reply_to_email' => $email,
    'reply_to_name' => $fullName,
    'form_type' => 'quote',
    'direction' => 'admin',
    'submission_table' => 'quote_requests',
    'submission_id' => $submissionId,
]);

$customerEmail = kwikshift_prepare_customer_email([
    'customer_name' => $fullName,
    'subject' => 'We received your moving quote request | Kwikshift Movers',
    'preview_text' => 'Your moving quote request is with our team and we will contact you shortly.',
    'heading' => 'Thank You for Your Quote Request',
    'subheading' => 'We have received your moving details and our team is preparing the next step.',
    'paragraph_one' => 'Thank you for requesting a moving quote from Kwikshift Movers. We have successfully received your pickup point, destination, move type, and support notes.',
    'paragraph_two' => 'One of our relocation specialists will contact you shortly to confirm the details, answer your questions, and guide you to the most suitable moving solution for your request.',
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
        'form_type' => 'quote',
        'direction' => 'client',
        'submission_table' => 'quote_requests',
        'submission_id' => $submissionId,
    ]);

// ===== STATUS UPDATE PHASE =====
$emailStatus = ($adminResult['ok'] && $clientResult['ok']) ? 'emailed' : 'email_failed';

try {
    kwikshift_db_update_by_id('quote_requests', $submissionId, [
        'status' => $emailStatus,
    ]);
} catch (Throwable $exception) {
    kwikshift_write_log('forms', 'error', 'quote_status_update_failed', 'Could not update submission status after email send.', [
        'submission_id' => $submissionId,
        'intended_status' => $emailStatus,
        'exception' => $exception->getMessage(),
    ]);
}

// ===== LOGGING PHASE =====
kwikshift_log_form_event(
    'quote',
    $emailStatus,
    $emailStatus === 'emailed'
        ? 'Quote request stored and notification emails processed successfully.'
        : 'Quote request stored but one or more emails failed to send.',
    [
        'submission_id' => $submissionId,
        'admin_email_sent' => $adminResult['ok'] ? 'yes' : 'no',
        'client_email_sent' => $clientResult['ok'] ? 'yes' : 'no',
        'admin_email_error' => !$adminResult['ok'] ? ($adminResult['message'] ?? 'Unknown error') : null,
        'client_email_error' => !$clientResult['ok'] ? ($clientResult['message'] ?? 'Unknown error') : null,
    ],
    (string) $sourcePage,
    'quote_requests',
    $submissionId
);

// ===== RESPONSE PHASE =====
kwikshift_json_response(true, 'Thank you! Your quote request has been received and our team will contact you shortly.', 200, [
    'submission_id' => $submissionId,
    'email_status' => $emailStatus,
]);
