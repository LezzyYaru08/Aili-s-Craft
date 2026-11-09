<?php
/**
 * Read-only source directory API for the AilisCraft Admin Source Code page.
 * Place this file in AilisCraftAdmin/. Requires PHP 8.0+.
 * IMPORTANT: protect this endpoint with real server-side admin authentication before deployment.
 */
declare(strict_types=1);

// Deployment gate: the source-code browser exposes every source file. It is
// DISABLED by default in production. Enable only on a trusted, authenticated
// host by setting AC_ENABLE_SOURCE_BROWSER=1 (see api/config.php).
require_once __DIR__ . '/api/config.php';
if (!AC_ENABLE_SOURCE_BROWSER) {
    http_response_code(403);
    exit('Source code browser is disabled on this deployment. Set AC_ENABLE_SOURCE_BROWSER=1 on a trusted host to enable it.');
}

$projectRoot = realpath(dirname(__DIR__));
if ($projectRoot === false) {
    http_response_code(500);
    exit('Project root could not be resolved.');
}

$modules = [
    'admin' => ['label' => 'Admin System Files', 'directory' => 'AilisCraftAdmin'],
    'owner' => ['label' => 'Owner System Files', 'directory' => 'AilisCraftOwner'],
    'user'  => ['label' => 'User System Files',  'directory' => 'AilisCraftUser'],
];
$excludedDirectories = ['.git', '.kilo', '.vscode', 'node_modules', 'vendor', '__pycache__'];

function respondJson(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
function vscodeUri(string $path): string {
    // Keep path separators intact while escaping spaces and other URI-sensitive characters.
    $normalized = str_replace('\\', '/', $path);
    return 'vscode://file/' . str_replace(['%2F', '%3A'], ['/', ':'], rawurlencode($normalized));
}
function collectFiles(string $base, string $relative = '', array $excluded = []): array {
    $current = $relative === '' ? $base : $base . DIRECTORY_SEPARATOR . $relative;
    $items = @scandir($current);
    if ($items === false) return [];
    $files = [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || str_starts_with($item, '.')) continue;
        $rel = $relative === '' ? $item : $relative . '/' . $item;
        $full = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
        if (is_link($full)) continue; // Do not follow symlinks outside the module.
        if (is_dir($full)) {
            if (in_array($item, $excluded, true)) continue;
            $files = array_merge($files, collectFiles($base, $rel, $excluded));
        } elseif (is_file($full)) {
            $real = realpath($full);
            if ($real === false || !str_starts_with($real, $base . DIRECTORY_SEPARATOR)) continue;
            $files[] = [
                'name' => $item,
                'path' => $rel,
                'absolutePath' => $real,
                'vscodeUri' => vscodeUri($real),
                'size' => filesize($real) ?: 0,
                'extension' => strtolower(pathinfo($item, PATHINFO_EXTENSION)),
            ];
        }
    }
    return $files;
}

$action = $_GET['action'] ?? 'list';
$moduleKey = $_GET['module'] ?? '';

if ($action === 'root') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['rootPath' => $projectRoot, 'vscodeUri' => vscodeUri($projectRoot)], JSON_UNESCAPED_SLASHES);
    exit;
}

if ($action === 'view') {
    if (!isset($modules[$moduleKey])) {
        http_response_code(400); exit('Unknown module.');
    }
    $relative = str_replace('\\', '/', (string)($_GET['path'] ?? ''));
    if ($relative === '' || str_starts_with($relative, '/') || preg_match('/(^|\/)\.\.?($|\/)/', $relative)) {
        http_response_code(400); exit('Invalid file path.');
    }
    $moduleRoot = realpath($projectRoot . DIRECTORY_SEPARATOR . $modules[$moduleKey]['directory']);
    if ($moduleRoot === false) { http_response_code(404); exit('Module directory not found.'); }
    $target = realpath($moduleRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative));
    if ($target === false || !is_file($target) || !str_starts_with($target, $moduleRoot . DIRECTORY_SEPARATOR)) {
        http_response_code(404); exit('File not found.');
    }
    $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
    $textExtensions = ['html','htm','css','js','mjs','cjs','json','md','txt','php','xml','svg','yml','yaml','sql','csv','env.example'];
    if (!in_array($ext, $textExtensions, true)) {
        http_response_code(415); exit('Preview is available for text-based source files only. Use Open in VS Code for this file.');
    }
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: inline; filename="' . basename($target) . '"');
    header('X-Content-Type-Options: nosniff');
    readfile($target);
    exit;
}

if ($action !== 'list') respondJson(['error' => 'Unknown action.'], 400);
$result = [];
foreach ($modules as $key => $module) {
    $dir = realpath($projectRoot . DIRECTORY_SEPARATOR . $module['directory']);
    if ($dir === false || !is_dir($dir) || !str_starts_with($dir, $projectRoot . DIRECTORY_SEPARATOR)) {
        $result[$key] = ['label' => $module['label'], 'directory' => $module['directory'], 'absolutePath' => null, 'vscodeUri' => null, 'files' => []];
        continue;
    }
    $result[$key] = [
        'label' => $module['label'],
        'directory' => $module['directory'] . '/',
        'absolutePath' => $dir,
        'vscodeUri' => vscodeUri($dir),
        'files' => collectFiles($dir, '', $excludedDirectories),
    ];
}
respondJson(['rootPath' => $projectRoot, 'rootVscodeUri' => vscodeUri($projectRoot), 'modules' => $result]);
