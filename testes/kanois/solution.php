<?php

for ($x=1; $x<=100; $x++) {

    $displayNumber = true;

    if ($x % 5 == 0) {
        echo 'Ka';
        $displayNumber = false;
    }

    if ($x % 7 == 0) {
        echo 'Nois';
        $displayNumber = false;
    }

    if ($displayNumber) {
        echo $x;
    }

    echo PHP_EOL;
}