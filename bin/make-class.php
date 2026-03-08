#!/usr/bin/env php
<?php

/**
 * WP Starter — Make Class CLI
 *
 * Creates a new PHP class file, then automatically wires it into the main
 * plugin file's includes() and init_plugin() methods.
 *
 * Usage:
 *   php bin/make-class.php <ClassName> [folder]
 *
 * Examples:
 *   php bin/make-class.php MyFeature
 *   php bin/make-class.php PostTypes includes
 *   php bin/make-class.php Menu_Widget widgets
 */

// ─── Bootstrap ────────────────────────────────────────────────────────────────

$rootDir = dirname(__DIR__);

// ─── Arguments ────────────────────────────────────────────────────────────────

$className = $argv[1] ?? null;
$folder    = isset($argv[2]) ? rtrim($argv[2], '/\\') : 'includes';

if (!$className) {
    echo <<<HELP

  Usage:  php bin/make-class.php <ClassName> [folder]

  ClassName   PascalCase or Snake_Case class name (e.g. MyFeature, Post_Types)
  folder      Sub-directory for the file           (default: includes)

  Examples:
    php bin/make-class.php MyFeature
    php bin/make-class.php PostTypes    includes
    php bin/make-class.php Menu_Widget  widgets

HELP;
    exit(1);
}

// Validate class name: letters, digits, underscores only
if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $className)) {
    echo "Error: '$className' is not a valid PHP class name.\n";
    exit(1);
}

// ─── Derived names ────────────────────────────────────────────────────────────

/**
 * Convert a class name to a kebab-case filename.
 *   MyFeature   → my-feature.php
 *   Post_Types  → post-types.php
 *   AssetsManager → assets-manager.php
 */
function class_to_filename(string $name): string
{
    $name = str_replace('_', '-', $name);
    $name = preg_replace('/([a-z\d])([A-Z])/', '$1-$2', $name);
    return strtolower($name) . '.php';
}

/**
 * Convert a folder path to a namespace segment.
 *   includes → Includes
 *   my/sub   → My\Sub
 */
function folder_to_ns_segment(string $folder): string
{
    return implode(
        '\\',
        array_map('ucfirst', explode('/', str_replace('\\', '/', $folder)))
    );
}

$fileName  = class_to_filename($className);
$filePath  = $rootDir . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $fileName;

// ─── Detect base namespace from existing files ────────────────────────────────

function detect_base_namespace(string $rootDir): string
{
    $search = [
        $rootDir . '/includes',
        $rootDir . '/widgets',
    ];
    foreach ($search as $dir) {
        foreach (glob($dir . '/*.php') ?: [] as $file) {
            if (preg_match('/^namespace\s+([A-Za-z\\\\]+)\\\\/m', file_get_contents($file), $m)) {
                return $m[1]; // e.g. "WPPluginStarter"
            }
        }
    }
    return 'WPPluginStarter'; // fallback
}

$baseNamespace = detect_base_namespace($rootDir);
$namespace     = $baseNamespace . '\\' . folder_to_ns_segment($folder);
$fqcn          = $namespace . '\\' . $className;

// ─── Find main plugin file ────────────────────────────────────────────────────

$mainPluginFile = null;
foreach (glob($rootDir . '/*.php') ?: [] as $phpFile) {
    if (strpos(file_get_contents($phpFile), 'Plugin Name:') !== false) {
        $mainPluginFile = $phpFile;
        break;
    }
}

if (!$mainPluginFile) {
    echo "Error: Could not find the main plugin file (no 'Plugin Name:' header found in root).\n";
    exit(1);
}

// ─── Guard: file must not already exist ──────────────────────────────────────

if (file_exists($filePath)) {
    echo "Error: File already exists: {$folder}/{$fileName}\n";
    exit(1);
}

// ─── Create directory if needed ───────────────────────────────────────────────

if (!is_dir(dirname($filePath))) {
    mkdir(dirname($filePath), 0755, true);
}

// ─── Write the class file ─────────────────────────────────────────────────────

$classContent = <<<PHP
<?php

namespace {$namespace};

defined('ABSPATH') || die();

class {$className} {
    public function __construct() {
        // TODO: register your WordPress hooks here
    }
}
PHP;

file_put_contents($filePath, $classContent);
echo "Created:  {$folder}/{$fileName}\n";

// ─── Helpers ──────────────────────────────────────────────────────────────────

/**
 * Insert $newStatement as a new line inside the method matched by $methodRegex.
 * The inserted line uses the same indentation as the last non-empty body line,
 * so it works regardless of whether the file uses tabs or spaces.
 *
 * Returns [$newContent, $success].
 */
function insert_into_method(string $content, string $methodRegex, string $newStatement): array
{
    $lines   = explode("\n", $content);
    $total   = count($lines);

    $inMethod          = false;
    $methodIndent      = -1;   // leading whitespace length of the "function" line
    $lastBodyLineIdx   = -1;
    $lastBodyIndent    = '';

    for ($i = 0; $i < $total; $i++) {
        $line    = $lines[$i];
        $trimmed = ltrim($line);
        $indent  = strlen($line) - strlen($trimmed);

        if (!$inMethod) {
            if (preg_match($methodRegex, $line)) {
                $inMethod     = true;
                $methodIndent = $indent;
            }
            continue;
        }

        // Closing brace of the method: a lone '}' at the same indent level as "function"
        if ($trimmed === '}' && $indent <= $methodIndent) {
            break;
        }

        if ($trimmed !== '') {
            $lastBodyLineIdx = $i;
            $lastBodyIndent  = str_repeat($line[0] === "\t" ? "\t" : ' ', $indent);
        }
    }

    if ($lastBodyLineIdx === -1) {
        return [$content, false];
    }

    array_splice($lines, $lastBodyLineIdx + 1, 0, [$lastBodyIndent . $newStatement]);
    return [implode("\n", $lines), true];
}

// ─── Patch the main plugin file ───────────────────────────────────────────────

$pluginContent = file_get_contents($mainPluginFile);
$pluginName    = basename($mainPluginFile);
$changed       = false;

// 1. Add require_once inside includes() ───────────────────────────────────────
$includeStatement = "require_once WPPS_PLUGIN_DIR . '{$folder}/{$fileName}';";

if (strpos($pluginContent, $includeStatement) !== false) {
    echo "Notice:   Include already present in includes().\n";
} else {
    [$patched, $ok] = insert_into_method(
        $pluginContent,
        '/\bfunction\s+includes\s*\(/',
        $includeStatement
    );

    if ($ok) {
        $pluginContent = $patched;
        $changed       = true;
        echo "Updated:  {$pluginName} → includes() ← {$includeStatement}\n";
    } else {
        echo "Warning:  Could not patch includes(). Add this line manually:\n";
        echo "          {$includeStatement}\n";
    }
}

// 2. Add instantiation inside init_plugin() ───────────────────────────────────
$initStatement = "new {$fqcn}();";

if (strpos($pluginContent, $initStatement) !== false) {
    echo "Notice:   Instantiation already present in init_plugin().\n";
} else {
    [$patched, $ok] = insert_into_method(
        $pluginContent,
        '/\bfunction\s+init_plugin\s*\(/',
        $initStatement
    );

    if ($ok) {
        $pluginContent = $patched;
        $changed       = true;
        echo "Updated:  {$pluginName} → init_plugin() ← new {$fqcn}()\n";
    } else {
        echo "Warning:  Could not patch init_plugin(). Add this line manually:\n";
        echo "          {$initStatement}\n";
    }
}

if ($changed) {
    file_put_contents($mainPluginFile, $pluginContent);
}

echo "\nDone! Class '{$className}' is ready in {$folder}/{$fileName}\n";
