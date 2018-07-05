#!/usr/bin/php
<?php

require './vendor/autoload.php';

use LowerSpeck\Checker;
use LowerSpeck\Reporter;

$args = collect($argv);

if ($args->contains('-vv')) {
    $verbosity = Reporter::VERY_VERBOSE;
} else if ($args->contains('-v')) {
    $verbosity = Reporter::VERBOSE;
} else {
    $verbosity = Reporter::NORMAL;
}

$id = $args->first(function ($arg) {
    return preg_match('/^\d+[\.a-z]*$/', $arg);
});

$checker = new Checker('.');

$reporter = new Reporter($checker->check($id), $verbosity);

$reporter->report();
