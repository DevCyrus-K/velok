<?php
declare(strict_types=1);

require_once __DIR__ . '/email-helper.php';

kwikshift_require_post();

$sourcePage = $_POST['source_page'] ?? '';

if (kwikshift_env_bool('FORM_CSRF_PROTECTION_ENABLED', true) && !kwikshift_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    kwikshift_log_form_event('review', 'rejected', 'Invalid CSRF token for review form.', [], (string) $sourcePage);
    kwikshift_json_response(false, 'Your secure session expired. Please refresh the page and try again.', 419);
}

$requestContext = kwikshift_submission_context((string) $sourcePage);

if (
    kwikshift_env_bool('FORM_RATE_LIMITING_ENABLED', true) &&
    kwikshift_is_rate_limited(
        'review',
        $requestContext['ip_address'],
        kwikshift_env_int('REVIEW_FORM_RATE_LIMIT_MAX_ATTEMPTS', 3, 1, 100),
        kwikshift_env_int('REVIEW_FORM_RATE_LIMIT_WINDOW_SECONDS', 3600, 60, 86400)
    )
) {
    kwikshift_log_form_event('review', 'rate_limited', 'Review form rate limit exceeded.', [], (string) $sourcePage);
    kwikshift_json_response(false, 'Too many reviews were submitted from your connection. Please wait a while and try again.', 429);
}

// ===== VALIDATION PHASE =====
$name = kwikshift_clean_text((string) ($_POST['review-name'] ?? ''), 120);
$role = kwikshift_clean_text((string) ($_POST['review-role'] ?? ''), 120);
$ratingRaw = str_replace(',', '.', trim((string) ($_POST['review-rating'] ?? '')));
$rating = (float) $ratingRaw;
$review = kwikshift_clean_textarea((string) ($_POST['review-message'] ?? ''), 4000);
$photo = $_FILES['review-photo'] ?? null;

$isValidRating = preg_match('/^(?:0\.5|[1-4](?:\.0|\.5)?|5(?:\.0)?)$/', $ratingRaw) === 1;
if ($isValidRating) {
    $rating = round($rating * 2) / 2;
}
$errors = [];

if ($name === '') {
    $errors['review-name'] = 'Please enter your full name.';
}

if ($role === '') {
    $errors['review-role'] = 'Please tell us who you are, for example Homeowner or Business Owner.';
}

if (!$isValidRating) {
    $errors['review-rating'] = 'Please choose a star rating before submitting your review.';
}

if (!is_array($photo) || (int) ($photo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
    $errors['review-photo'] = 'Please upload your photo.';
}

if ($review === '') {
    $errors['review-message'] = 'Please write your review before submitting.';
} elseif (mb_strlen($review) < 20) {
    $errors['review-message'] = 'Please make your review a little more detailed so it is useful to future customers.';
}

if ($errors !== []) {
    kwikshift_log_form_event('review', 'validation_failed', 'Review form validation failed.', [
        'errors' => implode(', ', array_keys($errors)),
        'review_name' => $name,
    ], (string) $sourcePage);
    kwikshift_json_validation_response('Please review the highlighted fields and try again.', $errors, 422);
}

// ===== FILE UPLOAD PHASE =====
$uploadDir = dirname(__DIR__) . '/assets/uploads/reviews';
$storedFile = null;

try {
    $storedFile = kwikshift_store_uploaded_file(
        $photo,
        [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ],
        $uploadDir,
        $name . '-review',
        5 * 1024 * 1024
    );
} catch (Throwable $exception) {
    kwikshift_log_form_event('review', 'upload_failed', 'Review image upload failed.', [
        'exception_message' => substr($exception->getMessage(), 0, 200),
        'review_name' => $name,
    ], (string) $sourcePage);
    
    kwikshift_json_validation_response($exception->getMessage(), [
        'review-photo' => $exception->getMessage(),
    ], 422);
}

$relativeImagePath = 'assets/uploads/reviews/' . $storedFile['filename'];
$absoluteImagePath = $storedFile['absolute_path'];
$formattedRating = abs($rating - round($rating)) < 0.001
    ? number_format($rating, 0, '.', '')
    : number_format($rating, 1, '.', '');

// ===== DATABASE INSERTION PHASE =====
$testimonialId = null;

try {
    $pdo = kwikshift_db();
    $pdo->beginTransaction();

    $testimonialId = kwikshift_db_insert('testimonials', [
        'client_name' => $name,
        'client_role' => $role,
        'testimonial_message' => $review,
        'client_image' => $relativeImagePath,
        'rating' => $rating,
        'service_type' => 'Customer Review',
        'featured' => 1,
        'status' => 'published',
    ]);

    $existingClient = kwikshift_db_fetch_one(
        'SELECT id
        FROM clients
        WHERE client_name = :client_name
            AND industry = :industry
        LIMIT 1',
        [
            'client_name' => $name,
            'industry' => $role,
        ]
    );

    if ($existingClient !== null) {
        kwikshift_db_query(
            'UPDATE clients
            SET client_logo = :client_logo,
                featured = :featured,
                status = :status,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id',
            [
                'client_logo' => $relativeImagePath,
                'featured' => 1,
                'status' => 'active',
                'id' => (int) $existingClient['id'],
            ]
        );
    } else {
        kwikshift_db_insert('clients', [
            'client_name' => $name,
            'client_logo' => $relativeImagePath,
            'client_website' => null,
            'industry' => $role,
            'featured' => 1,
            'status' => 'active',
        ]);
    }

    $pdo->commit();
} catch (Throwable $exception) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    @unlink($absoluteImagePath);
    
    kwikshift_log_form_event('review', 'db_failed', 'Failed to store review submission in database.', [
        'exception_message' => substr($exception->getMessage(), 0, 200),
        'review_name' => $name,
    ], (string) $sourcePage);
    
    kwikshift_write_log('forms', 'error', 'review_store_failed', 'Review submission could not be stored.', [
        'exception' => $exception->getMessage(),
        'review_name' => $name,
    ]);
    
    kwikshift_json_response(false, 'We could not process your review right now. Please try again.', 500);
}

if (!$testimonialId || $testimonialId < 1) {
    @unlink($absoluteImagePath);
    
    kwikshift_log_form_event('review', 'db_failed', 'Database insert returned invalid testimonial ID.', [
        'testimonial_id' => $testimonialId ?? 'null',
        'review_name' => $name,
    ], (string) $sourcePage);
    
    kwikshift_json_response(false, 'We could not process your review right now. Please try again.', 500);
}

// ===== EMAIL SENDING PHASE =====
$config = kwikshift_email_config();
$publicImageUrl = rtrim($config['base_url'], '/') . '/' . ltrim($relativeImagePath, '/');

$adminFields = [
    'Review ID' => (string) $testimonialId,
    'Timestamp' => date('Y-m-d H:i:s'),
    'Name' => $name,
    'Who They Are' => $role,
    'Rating' => $formattedRating . ' / 5',
    'Review' => $review,
    'Image URL' => $publicImageUrl,
    'Source Page' => $requestContext['source_page'],
    'IP Address' => $requestContext['ip_address'],
];

$adminResult = kwikshift_send_email([
    'to' => $config['admin_email'],
    'subject' => 'New customer review from ' . $name,
    'text' => kwikshift_render_admin_text('New customer review submission', $adminFields),
    'html' => kwikshift_render_admin_html('New customer review submission', $adminFields),
    'reply_to_email' => $config['contact_email'],
    'reply_to_name' => $config['sender_name'],
    'form_type' => 'review',
    'direction' => 'admin',
    'submission_table' => 'testimonials',
    'submission_id' => $testimonialId,
]);

// ===== LOGGING PHASE =====
kwikshift_log_form_event(
    'review',
    $adminResult['ok'] ? 'submitted' : 'email_failed',
    $adminResult['ok']
        ? 'Review submission stored and admin notification sent successfully.'
        : 'Review submission stored but the admin notification email failed.',
    [
        'testimonial_id' => $testimonialId,
        'admin_email_sent' => $adminResult['ok'] ? 'yes' : 'no',
        'admin_email_error' => !$adminResult['ok'] ? ($adminResult['message'] ?? 'Unknown error') : null,
    ],
    (string) $sourcePage,
    'testimonials',
    $testimonialId
);

// ===== RESPONSE PHASE =====
kwikshift_json_response(true, 'Thank you! Your review and photo have been submitted successfully.', 200, [
    'submission_id' => $testimonialId,
]);
