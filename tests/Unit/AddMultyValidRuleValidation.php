<?php

use Validator\Rule\ValidPool;
use Validator\Validator;

// can add multi validate rule
it('can add multy field using method field', function () {
    $valid = new Validator(['test' => 'test', 'test2' => 'test']);

    $valid->field('test', 'test2')->required();

    expect($valid->is_valid())->toBeTrue();
});

it('can add multy field using method __invoke', function () {
    $valid = new Validator(['test' => 'test', 'test2' => 'test']);

    $valid('test', 'test2')->required();

    expect($valid->is_valid())->toBeTrue();
});

it('can add validator rule using pools callback', function () {
    $v = Validator::make(['test' => 123])
        ->valid_pool(fn (ValidPool $v) => [
            $v('test')->required(),
            $v('d')->alpha(),
        ])->is_valid();

    expect($v)->toBeTrue();
});
