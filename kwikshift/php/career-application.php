<?php
declare(strict_types=1);

require_once __DIR__ . '/email-helper.php';

kwikshift_require_post();

$sourcePage = $_POST['source_page'] ?? '';
$jobId = (int) ($_POST['job_id'] ?? 0);

if (kwikshift_env_bool('FORM_CSRF_PROTECTION_ENABLED', true) && !kwikshift_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    kwikshift_log_form_event('career_application', 'rejected', 'Invalid CSRF token for career application.', [], (string) $sourcePage);
    kwikshift_json_response(false, 'Your secure session expired. Please refresh the page and try again.', 419);
}

$requestContext = kwikshift_submission_context((string) $sourcePage);

if (
    kwikshift_env_bool('FORM_RATE_LIMITING_ENABLED', true) &&
    kwikshift_is_rate_limited(
        'career_application',
        $requestContext['ip_address'],
        kwikshift_env_int('CAREER_FORM_RATE_LIMIT_MAX_ATTEMPTS', 4, 1, 100),
        kwikshift_env_int('CAREER_FORM_RATE_LIMIT_WINDOW_SECONDS', 3600, 60, 86400)
    )
) {
    kwikshift_log_form_event('career_application', 'rate_limited', 'Career application rate limit exceeded.', [], (string) $sourcePage);
    kwikshift_json_response(false, 'Too many applications were sent from your connection. Please wait a while and try again.', 429);
}

// ===== VALIDATION PHASE =====
$fullName = kwikshift_clean_text((string) ($_POST['full_name'] ?? ''), 120);
$email = kwikshift_clean_email((string) ($_POST['email'] ?? ''));
$phone = kwikshift_clean_phone((string) ($_POST['phone'] ?? ''), 32);
$coverLetter = kwikshift_clean_textarea((string) ($_POST['cover_letter'] ?? ''), 10000);
$cvFile = $_FILES['cv_file'] ?? null;
$errors = [];

if ($fullName === '') {
    $errors['full_name'] = 'Please enter your full name.';
}

if ($email === '') {
    $errors['email'] = 'Please enter your email address.';
} elseif (!kwikshift_validate_email($email)) {
    $errors['email'] = 'Please enter a valid email address.';
}

if ($phone === '') {
    $errors['phone'] = 'Please enter your phone number.';
} elseif (!kwikshift_validate_phone($phone)) {
    $errors['phone'] = 'Please enter a valid phone number.';
}

if (!is_array($cvFile) || (int) ($cvFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    $errors['cv_file'] = 'Please upload your CV.';
}

if ($coverLetter === '') {
    $errors['cover_letter'] = 'Please write a short cover letter before applying.';
} elseif (mb_strlen($coverLetter) < 30) {
    $errors['cover_letter'] = 'Please provide a short cover letter explaining why you are a strong fit for this role.';
}

if ($jobId < 1) {
    kwikshift_log_form_event('career_application', 'validation_failed', 'Career application missing a valid job reference.', [
        'job_id' => $jobId,
    ], (string) $sourcePage);
    kwikshift_json_response(false, 'Please choose an active role from the careers page before submitting your application.', 422);
}

if ($errors !== []) {
    kwikshift_log_form_event('career_application', 'validation_failed', 'Career application validation failed.', [
        'errors' => implode(', ', array_keys($errors)),
        'email' => $email,
        'job_id' => $jobId,
    ], (string) $sourcePage);
    kwikshift_json_validation_response('Please review the highlighted application details and try again.', $errors, 422);
}

// ===== JOB VERIFICATION PHASE =====
$career = kwikshift_db_fetch_one(
    'SELECT id, job_title, department, location, employment_type, deadline, application_email
    FROM careers
    WHERE id = :id
        AND status = :status
    LIMIT 1',
    [
        'id' => $jobId,
        'status' => 'open',
    ]
);

if ($career === null) {
    kwikshift_log_form_event('career_application', 'validation_failed', 'Career application was submitted for an unavailable job.', [
        'job_id' => $jobId,
        'email' => $email,
    ], (string) $sourcePage);
    kwikshift_json_response(false, 'This job is no longer available for applications.', 404);
}

// ===== FILE UPLOAD PHASE =====
$storedCv = null;

try {
    $storedCv = kwikshift_store_uploaded_file(
        $cvFile,
        [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        ],
        KWIKSHIFT_PRIVATE_UPLOAD_DIR . '/career-cvs',
        $fullName . '-' . (string) $career['job_title'],
        5 * 1024 * 1024
    );
} catch (Throwable $exception) {
    kwikshift_log_form_event('career_application', 'upload_failed', 'Career application CV upload failed.', [
        'exception_message' => substr($exception->getMessage(), 0, 200),
        'email' => $email,
        'job_id' => $jobId,
    ], (string) $sourcePage);
    
    kwikshift_json_validation_response($exception->getMessage(), [
        'cv_file' => $exception->getMessage(),
    ], 422);
}

$relativeCvPath = 'php/storage/uploads/career-cvs/' . $storedCv['filename'];

// ===== DATABASE INSERTION PHASE =====
$submissionId = null;

try {
    $submissionId = kwikshift_db_insert('career_applications', [
        'job_id' => $jobId,
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'cv_file' => $relativeCvPath,
        'cover_letter' => $coverLetter,
        'source_page' => $requestContext['source_page'],
        'ip_address' => $requestContext['ip_address'],
        'user_agent' => $requestContext['user_agent'],
        'status' => 'new',
    ]);
} catch (Throwable $exception) {
    @unlink($storedCv['absolute_path']);
    
    kwikshift_log_form_event('career_application', 'db_failed', 'Failed to store career application in database.', [
        'exception_message' => substr($exception->getMessage(), 0, 200),
        'email' => $email,
        'job_id' => $jobId,
    ], (string) $sourcePage);
    
    kwikshift_write_log('forms', 'error', 'career_application_store_failed', 'Career application could not be stored.', [
        'exception' => $exception->getMessage(),
        'email' => $email,
        'job_id' => $jobId,
    ]);
    
    kwikshift_json_response(false, 'We could not process your application right now. Please try again in a moment.', 500);
}

if (!$submissionId || $submissionId < 1) {
    @unlink($storedCv['absolute_path']);
    
    kwikshift_log_form_event('career_application', 'db_failed', 'Database insert returned invalid submission ID.', [
        'submission_id' => $submissionId ?? 'null',
        'email' => $email,
        'job_id' => $jobId,
    ], (string) $sourcePage);
    
    kwikshift_json_response(false, 'We could not process your application right now. Please try again in a moment.', 500);
}

// ===== EMAIL SENDING PHASE =====
$config = kwikshift_email_config();

$adminFields = [
    'Application ID' => (string) $submissionId,
    'Timestamp' => date('Y-m-d H:i:s'),
    'Job Title' => (string) $career['job_title'],
    'Department' => (string) $career['department'],
    'Location' => (string) $career['location'],
    'Employment Type' => (string) $career['employment_type'],
    'Deadline' => !empty($career['deadline']) ? (string) $career['deadline'] : 'Not provided',
    'Applicant Name' => $fullName,
    'Applicant Email' => $email,
    'Applicant Phone' => $phone,
    'Cover Letter' => $coverLetter,
    'Stored CV Path' => $relativeCvPath,
    'Source Page' => $requestContext['source_page'],
    'IP Address' => $requestContext['ip_address'],
];

$adminEmail = kwikshift_validate_email((string) $career['application_email'])
    ? (string) $career['application_email']
    : $config['admin_email'];

$adminResult = kwikshift_send_email([
    'to' => $adminEmail,
    'subject' => 'New job application for ' . (string) $career['job_title'] . ' from ' . $fullName,
    'text' => kwikshift_render_admin_text('New career application', $adminFields),
    'html' => kwikshift_render_admin_html('New career application', $adminFields),
    'reply_to_email' => $email,
    'reply_to_name' => $fullName,
    'attachments' => [
        [
            'path' => $storedCv['absolute_path'],
            'filename' => $storedCv['filename'],
        ],
    ],
    'form_type' => 'career_application',
    'direction' => 'admin',
    'submission_table' => 'career_applications',
    'submission_id' => $submissionId,
]);

$customerEmail = kwikshift_prepare_customer_email([
    'customer_name' => $fullName,
    'subject' => 'We received your application | Kwikshift Movers',
    'preview_text' => 'Your job application has been received by the Kwikshift Movers team.',
    'heading' => 'Thank You for Your Application',
    'subheading' => 'We have received your application and supporting CV successfully.',
    'paragraph_one' => 'Thank you for applying for the ' . (string) $career['job_title'] . ' role at Kwikshift Movers. Your application and CV have been received successfully by our recruitment team.',
    'paragraph_two' => 'Our team will review your application and contact you if your experience matches the next step for this opportunity. If you need to follow up urgently, please contact our office directly.',
    'closing_name' => 'KWIKSHIFT MOVERS Team',
]);

$clientResult = kwikshift_email_transport_unavailable($adminResult)
    ? kwikshift_email_skipped_result('Applicant confirmation email skipped because the SMTP transport is currently unavailable.')
    : kwikshift_send_email([
        'to' => $email,
        'subject' => $customerEmail['subject'],
        'text' => $customerEmail['text'],
        'html' => $customerEmail['html'],
        'inline_attachments' => $customerEmail['inline_attachments'],
        'reply_to_email' => $config['contact_email'],
        'reply_to_name' => $config['sender_name'],
        'auto_submitted' => $customerEmail['auto_submitted'],
        'form_type' => 'career_application',
        'direction' => 'client',
        'submission_table' => 'career_applications',
        'submission_id' => $submissionId,
    ]);

// ===== STATUS UPDATE PHASE =====
$emailStatus = ($adminResult['ok'] && $clientResult['ok']) ? 'emailed' : 'email_failed';

try {
    kwikshift_db_update_by_id('career_applications', $submissionId, [
        'status' => $emailStatus,
    ]);
} catch (Throwable $exception) {
    kwikshift_write_log('forms', 'error', 'career_status_update_failed', 'Could not update submission status after email send.', [
        'submission_id' => $submissionId,
        'intended_status' => $emailStatus,
        'exception' => $exception->getMessage(),
    ]);
}

// ===== LOGGING PHASE =====
kwikshift_log_form_event(
    'career_application',
    $emailStatus,
    $emailStatus === 'emailed'
        ? 'Career application stored and notification emails processed successfully.'
        : 'Career application stored but one or more emails failed to send.',
    [
        'submission_id' => $submissionId,
        'job_id' => $jobId,
        'admin_email_sent' => $adminResult['ok'] ? 'yes' : 'no',
        'client_email_sent' => $clientResult['ok'] ? 'yes' : 'no',
        'admin_email_error' => !$adminResult['ok'] ? ($adminResult['message'] ?? 'Unknown error') : null,
        'client_email_error' => !$clientResult['ok'] ? ($clientResult['message'] ?? 'Unknown error') : null,
    ],
    (string) $sourcePage,
    'career_applications',
    $submissionId
);

// ===== RESPONSE PHASE =====
kwikshift_json_response(true, 'Thank you! Your application has been received successfully and our recruitment team will review it shortly.', 200, [
    'submission_id' => $submissionId,
    'email_status' => $emailStatus,
]);
