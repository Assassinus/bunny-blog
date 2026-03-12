<?php

declare(strict_types=1);

/**
 * Compile SCSS to CSS using scssphp.
 *
 * Run: php bin/compile-scss.php
 * Docker: docker compose exec app php /var/www/html/bin/compile-scss.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\OutputStyle;

$projectRoot = dirname(__DIR__);
$inputFile = $projectRoot . '/assets/scss/style.scss';
$outputFile = $projectRoot . '/public/css/style.css';

$compiler = new Compiler();
$compiler->setImportPaths([$projectRoot . '/assets/scss']);
$compiler->setOutputStyle(OutputStyle::EXPANDED);

$scss = file_get_contents($inputFile);
if ($scss === false) {
    fwrite(STDERR, "Cannot read {$inputFile}\n");
    exit(1);
}

try {
    $result = $compiler->compileString($scss);
    $css = $result->getCss();
} catch (\Throwable $e) {
    fwrite(STDERR, "SCSS compilation error: {$e->getMessage()}\n");
    exit(1);
}

$outputDir = dirname($outputFile);
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

file_put_contents($outputFile, "/* Compiled from assets/scss/style.scss — do not edit directly */\n" . $css);
echo "Compiled: {$outputFile} (" . strlen($css) . " bytes)\n";
