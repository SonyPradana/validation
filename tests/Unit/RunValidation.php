<?php

use Validator\Validator;
use Validator\Rule\ValidPool;

// run validation
it('can run validation using method is_valid', function () {
    $valid = new Validator(['test' => 'test']);

    $valid->test->required();

    expect($valid->is_valid())->toBeTrue();
});

it('can run validation using method is_valid with closure (param)', function () {
    $valid = new Validator([
        'test1' => 'test',
        'test2' => 'test',
        'test3' => 'test',
        'test4' => 'test',
        'test5' => 'test',
        'test6' => 'test',
        'test7' => 'test',
    ]);

    expect($valid->is_valid(function (ValidPool $pool) {
        $pool->rule('test1')->required();
        $pool('test2')->required();
        $pool->test3->required();
        $pool->rule('test4', 'test5')->required();
        $pool('test6', 'test7')->required();
    }))->toBeTrue();
});

it('can run validation using method is_valid with closure (return)', function () {
    $valid = new Validator([
        'test1' => 'test',
        'test2' => 'test',
        'test3' => 'test',
        'test4' => 'test',
        'test5' => 'test',
    ]);

    expect($valid->is_valid(function () {
        $pool = new ValidPool();
        $pool->rule('test1')->required();
        $pool('test2')->required();
        $pool->test3->required();
        $pool->rule('test4', 'test5')->required();

        return $pool;
    }))->toBeTrue();
});

it('can run validation using method if_valid', function () {
    $valid = new Validator(['test' => 'test']);

    $valid->test->required();

    $valid->if_valid(function () {
        expect(true)->toBeTrue();
    })->else(function ($err) {
        expect($err)->toBe([]);
    });
});

it('can run validation using method validOrException', function () {
    $valid = new Validator(['test' => 'test']);

    $valid->test->required();

    expect($valid->validOrException())->toBeTrue();
});

it('can run validation using method validOrException but not valid', function () {
    $valid = new Validator(['test' => 'test']);

    $valid->test->required()->min_len(5);
    $valid->validOrException();
})->throws('vaildate if fallen');

it('can run validation using method validOrError', function () {
    $valid = new Validator(['test' => 'test']);

    $valid->test->required();

    expect($valid->validOrError())->toBeTrue();
});

it('can run validation using method validOrError but not valid', function () {
    $valid = new Validator(['test' => 'test']);

    $valid->test->required()->min_len(5);

    expect($valid->validOrError())->toBeArray();
});

test('method is_error is invert as is_valid', function () {
    $valid = new Validator(['test' => 'test']);

    $valid->test->required();

    expect($valid->is_error())->toBeFalse();
    expect($valid->is_error())->not->toEqual($valid->is_valid());
});
