<?php

declare(strict_types=1);

namespace Validator;

use Closure;
use Exception;
use Validator\Rule\Filter;
use Validator\Rule\FilterPool;
use Validator\Rule\Valid;
use Validator\Rule\ValidPool;

/**
 * @internal
 */
final class Validator
{
    private Rule $Rule;

    /** @var string[] */
    private $fields      = [];
    /** @var Valid[] */
    private $validations = [];
    /** @var Filter[] */
    private $filters = [];
    /** @var bool Check rule validate has run or not */
    private $has_run_validate = false;

    /**
     * Create validation and filter.
     *
     * @param string[] $fileds Field array to validate
     */
    public function __construct($fileds = [])
    {
        $this->Rule   = new Rule();
        $this->fields = $fileds;
    }

    /**
     * Add new valid rule.
     *
     * @param string $name Field name
     *
     * @return Valid New rule Validation
     */
    public function __get($name): Valid
    {
        return $this->field($name);
    }

    /**
     * Add new valid rule.
     *
     * @param string $field Field name
     *
     * @return Valid New rule Validation
     */
    public function __invoke(string $field): Valid
    {
        return $this->field($field);
    }

    /**
     * Add new valid rule.
     *
     * @param string $field Field name
     *
     * @return Valid New rule Validation
     */
    public function field(string $field): Valid
    {
        return $this->validations[$field] = new Valid();
    }

    /**
     * Add new filter rule.
     *
     * @param string $field Field name
     *
     * @return Filter New rule filter
     */
    public function filter(string $field): Filter
    {
        return $this->filters[$field] = new Filter();
    }

    /**
     * Set fields or input for validation.
     *
     * @param array<string, string> $fields Field array to validate
     */
    public function fields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * get fields or input validation.
     *
     * @return array<string, string> Fields
     */
    public function get_fields(): array
    {
        return $this->fields;
    }

    /**
     * Process the validation errors and return an array of errors with field names as keys.
     *
     * @return array<int, string> Validation errors
     *
     * @throws Exception
     */
    public function get_error(): array
    {
        if (!$this->has_run_validate) {
            $this->Rule->validate($this->fields, $this->validations);
            $this->has_run_validate = true;
        }

        return $this->Rule->get_errors_array();
    }

    /**
     * Inline validation field.
     *
     * @param \Closure|null $rule_validation Closure with param as ValidPool,
     *                                       if null return validate this currect validation
     */
    public function is_valid(?Closure $rule_validation = null): bool
    {
        if ($rule_validation == null) {
            $this->has_run_validate = true;

            return $this->Rule->validate($this->fields, $this->validations) !== true ? false : true;
        }

        $rules = [];
        $pool  = new ValidPool();

        $return_closure = call_user_func_array($rule_validation, [$pool]);
        $get_pool       = $return_closure instanceof ValidPool
            ? $return_closure->get_pool()
            : $pool->get_pool()
        ;

        foreach ($get_pool as $field => $rule) {
            $rules[$field] = $rule->get_validation();
        }

        $this->Rule->validation_rules($rules);
        if ($this->Rule->run($this->fields) === false) {
            return false;
        }

        return true;
    }

    /**
     * Execute closuer when validation is true,
     * and return else statment.
     *
     * @param Closure $condition Excute closure
     */
    public function if_valid(Closure $condition): ValidationCondition
    {
        $val = $this->Rule->validate($this->fields, $this->validations);

        if ($val === true) {
            call_user_func($condition);

            return new ValidationCondition([]);
        }

        return new ValidationCondition($val);
    }

    /**
     * Run validation, and throw error when false.
     *
     * @param \Exception|null $exception Default throw exception
     *
     * @throws Exception
     *
     * @return bool Return true if validation valid
     */
    public function validOrException(Exception $exception = null)
    {
        if ($this->Rule->validate($this->fields, $this->validations) === true) {
            return true;
        }

        throw $exception ?? new Exception('vaildate if fallen');
    }

    /**
     * Run validation, and get error when false.
     *
     * @return bool|array<int, string> Return true if validation valid
     */
    public function validOrError(Exception $exception = null)
    {
        return $this->Rule->validate($this->fields, $this->validations);
    }

    /**
     * Filter the input data.
     *
     * @return mixed, string> Fields input after filter
     */
    public function filter_out(?Closure $rule_filter = null)
    {
        if ($rule_filter == null) {
            return $this->Rule->filter($this->fields, $this->filters);
        }

        // overwrite input field
        $rules_filter          = $this->fields;
        $filter_pool           = new FilterPool();
        $return_filter_closure = call_user_func_array($rule_filter, [$filter_pool]);
        $get_filter_pool       = $return_filter_closure instanceof FilterPool
            ? $return_filter_closure->get_pool()
            : $filter_pool->get_pool()
        ;

        // replace input field with filter
        foreach ($get_filter_pool as $field => $rule) {
            $rules_filter[$field] = $rule->get_filter();
        }

        return $this->Rule->filter($this->fields, $rules_filter);
    }

    /**
     * Run validation and filter if success.
     *
     * @return bool|mixed True if validation failed,
     *                    array filter if validation valid
     */
    public function failedOrFilter()
    {
        if ($this->Rule->validate($this->fields, $this->validations) === true) {
            return $this->filter_out();
        }

        return true;
    }

    /**
     * Change language for error messages.
     * Can effect before run validation or filter.
     *
     * @param string $lang Language
     */
    public function lang(string $lang): self
    {
        $this->Rule->lang($lang);

        return $this;
    }
}
