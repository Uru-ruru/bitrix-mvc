<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('tests/')
    ->in(__DIR__)
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
;

$config = new PhpCsFixer\Config();
$config
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setRules(['@PhpCsFixer' => true, '@PHP81Migration' => true])
    ->setFinder($finder)
;

return $config;
