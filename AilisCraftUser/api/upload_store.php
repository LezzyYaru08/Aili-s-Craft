<?php
/**
 * Aili's Craft — upload storage adapter (special-request reference photos).
 *
 *   ac_store_image(string $tmpPath, string $ext, string $mime): string
 *
 * If AC_CLOUDINARY_CLOUD_NAME / _API_KEY / _API_SECRET are ALL set via env,
 * the image is uploaded (signed) to Cloudinary and the secure_url is returned.
 * Otherwise the local fallback saves to uploads/special_requests/ with a
 * uniqid('sr_', true) name and returns the relative path — byte-identical to
 * the pre-adapter behavior. A Cloudinary failure throws ApiError cleanly so
 * the upload endpoint returns a normal error (never a fatal).
 */
declare(strict_types=1);
require_once __DIR__ . '/config.php';
function ac_store_image(string $tmpPath, string $ext, string $mime): string
{
    $cloudName = ac_env('AC_CLOUDINARY_CLOUD_NAME', '');
    $apiKey    = ac_env('AC_CLOUDINARY_API_KEY', '');
    $apiSecret = ac_env('AC_CLOUDINARY_API_SECRET', '');
    if ($cloudName !== '' && $apiKey !== '' && $apiSecret !== '') {
        return ac_store_image_cloudinary($tmpPath, $mime, $cloudName, $apiKey, $apiSecret, $ext);
    }
    return ac_store_image_local($tmpPath, $ext);
}
/** Local fallback — exactly the pre-adapter behavior. */
function ac_store_image_local(string $tmpPath, string $ext): string
{
    $uploadDir = dirname(__DIR__) . '/uploads/special_requests/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0775, true);
    }
    $filename = uniqid('sr_', true) . '.' . $ext;
    $dest     = $uploadDir . $filename;
    if (!@move_uploaded_file($tmpPath, $dest)) {
        throw new ApiError('Could not save the uploaded file.', 500);
    }
    return 'uploads/special_requests/' . $filename;
}
/** Signed multipart upload to Cloudinary REST API. Throws ApiError on failure. */
function ac_store_image_cloudinary(string $tmpPath, string $mime, string $cloudName, string $apiKey, string $apiSecret, string $ext): string
{
    $timestamp = (string) time();
    $folder    = ac_env('AC_CLOUDINARY_FOLDER', '');
    // Signed params (alphabetical; exclude file, api_key, signature, resource_type).
    $params = ['timestamp' => $timestamp];
    if ($folder !== '') {
        $params['folder'] = $folder;
    }
    ksort($params);
    $serialized = [];
    foreach ($params as $k => $v) {
        $serialized[] = $k . '=' . $v;
    }
    $signature = sha1(implode('&', $serialized) . $apiSecret);
    $fileData = @file_get_contents($tmpPath);
    if ($fileData === false) {
        throw new ApiError('Could not read the uploaded file.', 500);
    }
    $boundary = '----AilisCraft' . bin2hex(random_bytes(8));
    $body = '';
    foreach ($params as $k => $v) {
        $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"$k\"\r\n\r\n$v\r\n";
    }
    $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"api_key\"\r\n\r\n$apiKey\r\n";
    $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"signature\"\r\n\r\n$signature\r\n";
    $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"file\"; filename=\"upload.$ext\"\r\nContent-Type: $mime\r\n\r\n" . $fileData . "\r\n";
    $body .= "--$boundary--\r\n";
    $url = 'https://api.cloudinary.com/v1_1/' . rawurlencode($cloudName) . '/image/upload';
    $ctx = stream_context_create([
        'http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: multipart/form-data; boundary=$boundary\r\n",
            'content'       => $body,
            'timeout'       => 30,
            'ignore_errors' => true,
        ],
    ]);
    $resp = @file_get_contents($url, false, $ctx);
    $data = json_decode((string) $resp, true);
    if (is_array($data) && !empty($data['secure_url'])) {
        return (string) $data['secure_url'];
    }
    $err = (is_array($data) && !empty($data['error']['message'])) ? $data['error']['message'] : substr((string) $resp, 0, 200);
    throw new ApiError('Image upload to storage failed: ' . $err, 502);
}
