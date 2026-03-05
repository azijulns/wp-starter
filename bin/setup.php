<?php

/**
 * WordPress Plugin Starter Kit — Interactive CLI Setup
 *
 * Auto-runs after:  composer create-project your-vendor/wp-plugin-starter my-plugin
 * Or run manually:  php bin/setup.php
 *
 * Tokens replaced across all plugin files:
 *   {{PLUGIN_NAME}}        → "My Awesome Plugin"
 *   {{PLUGIN_SLUG}}        → "my-awesome-plugin"
 *   {{PLUGIN_DESCRIPTION}} → "Does something great."
 *   {{PLUGIN_AUTHOR}}      → "Jane Doe"
 *   {{PLUGIN_AUTHOR_URL}}  → "https://janedoe.com"
 *   {{PLUGIN_TEXT_DOMAIN}} → "my-awesome-plugin"
 *   {{PLUGIN_NAMESPACE}}   → "MyAwesomePlugin"   (PascalCase, PHP namespace)
 *   {{PLUGIN_PREFIX}}      → "MAP"               (UPPER, constants & class names)
 *   {{plugin_prefix}}      → "map"               (lower, function names)
 *   {{plugin-prefix}}      → "map"               (lower, CSS/JS handles)
 */

// ─── Detect interactive vs. auto mode ────────────────────────────────────────
//
// Composer pipes stdin when running post-create-project-cmd, so there is no
// usable console on any OS.  We detect this and switch to "auto mode", which
// derives all plugin values from the project directory name — no prompts.
//
// Running `php bin/setup.php` directly always has an interactive terminal.

$interactive = @stream_isatty(STDIN);

// Unix/macOS: stdin may be piped but /dev/tty is still reachable
if (!$interactive && PHP_OS_FAMILY !== 'Windows' && @is_readable('/dev/tty')) {
    $interactive = true;
}

// ─── Collect values ───────────────────────────────────────────────────────

if ($interactive) {
    echo PHP_EOL;
    echo "╔══════════════════════════════════════════════════╗" . PHP_EOL;
    echo "║      WordPress Plugin Starter Kit Setup          ║" . PHP_EOL;
    echo "╚══════════════════════════════════════════════════╝" . PHP_EOL . PHP_EOL;
    echo "Answer the questions below. Press [Enter] to accept the default." . PHP_EOL . PHP_EOL;

    $pluginName  = ask('Plugin Name', 'My Awesome Plugin');
    $pluginSlug  = ask('Plugin Slug (folder & file name)', to_slug($pluginName));
    $prefix      = ask('Prefix for constants/classes (2-5 chars, e.g. MAP)', to_prefix($pluginSlug));
    $namespace   = ask('PHP Namespace (PascalCase, e.g. MyAwesomePlugin)', to_namespace($pluginSlug));
    $description = ask('Description', "Essential WordPress plugin – $pluginName");
    $author      = ask('Author Name', '');
    $authorUrl   = ask('Author URL', '');
    $textDomain  = ask('Text Domain', $pluginSlug);
} else {
    // Auto mode: derive everything from the project directory name.
    // e.g. `composer create-project blackdevs/wp-starter my-awesome-plugin`
    //  → directory = my-awesome-plugin → Plugin Name = My Awesome Plugin
    $pluginSlug  = to_slug(basename(getcwd()));
    $pluginName  = ucwords(str_replace('-', ' ', $pluginSlug));
    $prefix      = to_prefix($pluginSlug);
    $namespace   = to_namespace($pluginSlug);
    $description = "Essential WordPress plugin – $pluginName";
    $author      = '';
    $authorUrl   = '';
    $textDomain  = $pluginSlug;

    echo PHP_EOL . "  Auto-configuring from directory name: " . basename(getcwd()) . PHP_EOL;
}

// Derived case variants
$PREFIX      = strtoupper($prefix);          // MAP   → constants, class names
$plugin_     = strtolower($prefix);          // map   → function names (map_init_plugin)
$plugin_dash = strtolower($prefix);          // map   → CSS/JS handles (map-public)

// ─── Confirm ───────────────────────────────────────────────────────────────

echo PHP_EOL;
echo "─────────────────────────────────────────────────────" . PHP_EOL;
echo " Plugin Name   : $pluginName"                          . PHP_EOL;
echo " Slug / File   : $pluginSlug / $pluginSlug.php"        . PHP_EOL;
echo " Prefix        : {$PREFIX}_ / {$plugin_}_ / {$plugin_dash}-" . PHP_EOL;
echo " Namespace     : $namespace\\"                          . PHP_EOL;
echo " Text Domain   : $textDomain"                          . PHP_EOL;
echo " Author        : $author ($authorUrl)"                 . PHP_EOL;
echo " Description   : $description"                        . PHP_EOL;
echo "─────────────────────────────────────────────────────" . PHP_EOL . PHP_EOL;

if ($interactive) {
    $confirm = ask('Looks good? Generate plugin (y/n)', 'y');
    if (strtolower(trim($confirm)) !== 'y') {
        echo PHP_EOL . "Aborted. No files were changed." . PHP_EOL . PHP_EOL;
        exit(0);
    }
}

// ─── Token → value map ────────────────────────────────────────────────────
// Two kinds of tokens:
//   • PHP-code positions  → valid PHP placeholder identifiers (WPPS / WPPluginStarter / wpps)
//   • PHP-string positions → {{TOKEN}} mustache style (safe inside quoted strings)
// Longer / more-specific tokens must come first to avoid partial matches.

$tokens = [
    // ── PHP string tokens (inside quotes) ──────────────────────────────────
    '{{PLUGIN_NAME}}'        => $pluginName,
    '{{PLUGIN_SLUG}}'        => $pluginSlug,
    '{{PLUGIN_DESCRIPTION}}' => $description,
    '{{PLUGIN_AUTHOR}}'      => $author,
    '{{PLUGIN_AUTHOR_URL}}'  => $authorUrl,
    '{{PLUGIN_TEXT_DOMAIN}}' => $textDomain,
    '{{plugin_prefix}}'      => $plugin_,     // lower_under  – function names, widget names
    '{{plugin-prefix}}'      => $plugin_dash, // lower-dash   – CSS/JS handles

    // ── PHP code-position placeholders (valid PHP identifiers) ─────────────
    // Namespace  (must come before shorter WPPS_ match)
    'WPPluginStarter\\'      => $namespace . '\\',   // namespace declarations & usage
    'WPPluginStarter'        => $namespace,           // bare namespace reference

    // Constant / class prefix  (UPPER)
    'WPPS_'                  => $PREFIX . '_',        // WPPS_PLUGIN_DIR, WPPS_MAIN, …

    // Init function  (lower)
    'wpps_init_plugin'       => $plugin_ . '_init_plugin',

    // Widget class names  (must come after WPPS_ replacement)
    'WPPS_Menu_Widget'       => $PREFIX . '_Menu_Widget',
    'WPPS_Menu'              => $PREFIX . '_Menu',
];

// ─── Apply tokens to all files ────────────────────────────────────────────

echo PHP_EOL . "Applying tokens..." . PHP_EOL;

$rootDir    = realpath(__DIR__ . '/..');
$extensions = ['php', 'js', 'css', 'json', 'txt', 'md'];
$skipDirs   = ['vendor', 'node_modules', '.git', 'bin'];
$changed    = 0;

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    $path = $file->getPathname();

    // Skip unwanted directories
    foreach ($skipDirs as $skip) {
        if (strpos($path, DIRECTORY_SEPARATOR . $skip . DIRECTORY_SEPARATOR) !== false
            || str_ends_with(dirname($path), DIRECTORY_SEPARATOR . $skip)
        ) {
            continue 2;
        }
    }

    if (!in_array($file->getExtension(), $extensions, true)) {
        continue;
    }

    $original = file_get_contents($path);
    $updated  = str_replace(array_keys($tokens), array_values($tokens), $original);

    if ($original !== $updated) {
        file_put_contents($path, $updated);
        echo "  [updated] " . str_replace($rootDir . DIRECTORY_SEPARATOR, '', $path) . PHP_EOL;
        $changed++;
    }
}

echo PHP_EOL . "$changed file(s) updated." . PHP_EOL;

// ─── Rename main plugin file ──────────────────────────────────────────────

$oldMain = $rootDir . DIRECTORY_SEPARATOR . 'demo.php';
$newMain = $rootDir . DIRECTORY_SEPARATOR . $pluginSlug . '.php';

if (file_exists($oldMain) && $oldMain !== $newMain) {
    rename($oldMain, $newMain);
    echo "  [renamed] demo.php  →  $pluginSlug.php" . PHP_EOL;
} elseif (!file_exists($oldMain)) {
    echo "  [info] demo.php not found – skipping rename." . PHP_EOL;
}

// ─── Update composer.json package name ───────────────────────────────────

$composerPath = $rootDir . DIRECTORY_SEPARATOR . 'composer.json';
if (file_exists($composerPath)) {
    $composerJson = json_decode(file_get_contents($composerPath), true);
    if ($composerJson !== null) {
        // Suggest a vendor/package name based on author + slug
        $vendor = to_slug($author ?: 'your-vendor');
        $composerJson['name']        = "$vendor/$pluginSlug";
        $composerJson['description'] = $description;
        $composerJson['authors']     = [['name' => $author, 'homepage' => $authorUrl]];
        file_put_contents($composerPath, json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
        echo "  [updated] composer.json (name, description, authors)" . PHP_EOL;
    }
}

// ─── Self-cleanup ─────────────────────────────────────────────────────────

echo PHP_EOL;
$doCleanup = true;
if ($interactive) {
    $cleanup   = ask('Remove bin/setup.php now? (y/n)', 'y');
    $doCleanup = strtolower(trim($cleanup)) === 'y';
}
if ($doCleanup) {
    @unlink(__FILE__);
    // Remove bin/ dir if now empty
    $binDir = __DIR__;
    if (is_dir($binDir) && count(array_diff((array) scandir($binDir), ['.', '..'])) === 0) {
        @rmdir($binDir);
    }
    echo "  [removed] bin/setup.php" . PHP_EOL;
}

// ─── Done ─────────────────────────────────────────────────────────────────

echo PHP_EOL;
echo "✓ Plugin \"$pluginName\" is ready!" . PHP_EOL;
echo "  Drop the folder into  wp-content/plugins/$pluginSlug/" . PHP_EOL;
echo "  then activate it from the WordPress admin." . PHP_EOL . PHP_EOL;

// ═══════════════════════════════════════════════════════════════════════════
// Helper functions
// ═══════════════════════════════════════════════════════════════════════════

/**
 * Prompt the user for a value with an optional default.
 */
function ask(string $question, string $default = ''): string
{
    $hint  = $default !== '' ? " [$default]" : '';
    echo "  $question$hint: ";
    // On Unix, fall back to /dev/tty if stdin is somehow redirected
    if (!@stream_isatty(STDIN) && PHP_OS_FAMILY !== 'Windows') {
        $tty = @fopen('/dev/tty', 'r');
        if ($tty) {
            $input = trim((string) fgets($tty));
            fclose($tty);
            return $input === '' ? $default : $input;
        }
    }
    $input = trim((string) fgets(STDIN));
    return $input === '' ? $default : $input;
}

/**
 * Convert any string to a lowercase hyphenated slug.
 *   "My Awesome Plugin" → "my-awesome-plugin"
 */
function to_slug(string $text): string
{
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    return strtolower(trim((string) $text, '-'));
}

/**
 * Convert a slug to a short uppercase prefix (first letters of each word).
 *   "my-awesome-plugin" → "MAP"
 */
function to_prefix(string $slug): string
{
    $words  = explode('-', $slug);
    $prefix = implode('', array_map(fn($w) => strtoupper($w[0] ?? ''), $words));
    // Clamp to 5 chars max; fallback to first 3 letters of slug
    return $prefix !== '' ? substr($prefix, 0, 5) : strtoupper(substr(preg_replace('/[^a-z]/i', '', $slug), 0, 3));
}

/**
 * Convert a slug to PascalCase namespace.
 *   "my-awesome-plugin" → "MyAwesomePlugin"
 */
function to_namespace(string $slug): string
{
    return str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));
}

