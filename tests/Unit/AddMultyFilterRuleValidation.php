<?php

use Validator\Rule\FilterPool;
use Validator\Validator;

// can add multi filter rule
it('can add multy filter using method filter', function () {
    $valid = new Validator(['test' => ' test ', 'test2' => ' test ']);

    $valid->field('test', 'test2')->required();
    $valid->filter('test', 'test2')->trim();

    expect($valid->filter_out())->toMatchArray(
        ['test' => 'test', 'test2' => 'test']
    );
});

it('can add filter rule using pools callback', function () {
    $valid = new Validator(['test' => ' test ', 'test2' => ' test ']);

    $valid->field('test', 'test2')->required();
    $valid->filter_pool(fn (FilterPool $f) => [
        $f->test->trim(),
        $f->test2->trim(),
    ]);

    expect($valid->filter_out())->toMatchArray(
        ['test' => 'test', 'test2' => 'test']
    );
});
