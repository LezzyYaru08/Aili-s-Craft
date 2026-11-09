<?php
/**
 * Aili's Craft — Upload reference photos for a customization special request.
 *
 *   POST multipart/form-data  fields: images[0], images[1], ... (image/*, <2MB each)
 *   -> { ok: true, files: ["uploads/special_requests/abc123.png", ...] }
 *
 * Files are saved under AilisCraftUser/uploads/special_requests/ with a
 * uniqid-based name. The directory carries a .htaccess that disables PHP
 * execution. Returned paths are relative to the AilisCraftUser document root.
 */
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/upload_store.php';

try {
    require_method((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'), 'POST');

    // Local storage needs a writable directory; Cloudinary (read-only hosts like
    // Vercel) does not. Skip the local-dir gate when Cloudinary env is configured.
    $acUsingCloudinary = ac_env('AC_CLOUDINARY_CLOUD_NAME', '') !== ''
        && ac_env('AC_CLOUDINARY_API_KEY', '') !== ''
        && ac_env('AC_CLOUDINARY_API_SECRET', '') !== '';
    if (!$acUsingCloudinary) {
        $uploadDir = __DIR__ . '/../uploads/special_requests/';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }
        if (!is_dir($uploadDir) || !is_writable($uploadDir)) {
            throw new ApiError('Upload directory is not writable on the server.', 500);
        }
    }

    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxBytes     = 2 * 1024 * 1024; // 2 MB

    $collected = [];
    foreach ($_FILES as $field) {
        $names  = is_array($field['name'] ?? null) ? $field['name'] : [$field['name'] ?? null];
        $tmps   = is_array($field['tmp_name'] ?? null) ? $field['tmp_name'] : [$field['tmp_name'] ?? null];
        $sizes  = is_array($field['size'] ?? null) ? $field['size'] : [$field['size'] ?? 0];
        $errors = is_array($field['error'] ?? null) ? $field['error'] : [$field['error'] ?? UPLOAD_ERR_NO_FILE];

        $count = count($names);
        for ($i = 0; $i < $count; $i++) {
            $name  = (string) ($names[$i] ?? '');
            $tmp   = (string) ($tmps[$i] ?? '');
            $size  = (int) ($sizes[$i] ?? 0);
            $error = (int) ($errors[$i] ?? UPLOAD_ERR_NO_FILE);

            if ($error === UPLOAD_ERR_NO_FILE || $name === '' || $tmp === '') {
                continue;
            }
            if ($error !== UPLOAD_ERR_OK) {
                throw new ApiError('One of the files could not be uploaded (error code ' . $error . ').', 400);
            }
            if ($size <= 0 || $size > $maxBytes) {
                throw new ApiError('Each reference photo must be an image smaller than 2 MB.', 400);
            }
            if (!is_uploaded_file($tmp)) {
                throw new ApiError('Invalid file upload.', 400);
            }

            // Verify the actual content is an image (not just the extension).
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = $finfo ? (string) finfo_file($finfo, $tmp) : '';
            if ($finfo) {
                finfo_close($finfo);
            }
            if (!in_array($mime, $allowedMimes, true)) {
                throw new ApiError('Only JPG, PNG, GIF and WebP images are accepted.', 400);
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts, true)) {
                // Trust the detected mime over the client extension.
                $ext = ($mime === 'image/jpeg') ? 'jpg' : (($mime === 'image/png') ? 'png' : (($mime === 'image/gif') ? 'gif' : 'webp'));
            }

            // All validation (mime/size/is_uploaded_file/finfo) above is preserved.
            // ac_store_image picks Cloudinary (signed) or local fallback by env;
            // local fallback returns the same relative path as before.
            $collected[] = ac_store_image($tmp, $ext, $mime);
        }
    }

    json_out(['ok' => true, 'files' => $collected]);
} catch (ApiError $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], $e->status);
} catch (Throwable $e) {
    error_log('[AilisCraft upload_special_request] ' . $e);
    json_out(['ok' => false, 'error' => AC_DEBUG ? $e->getMessage() : 'Upload failed. Please try again.'], 500);
}
