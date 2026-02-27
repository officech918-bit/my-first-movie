<?php

namespace App\Services;

class Validator
{
    private $errors = [];
    private $data;

    public function make(array $data, array $rules)
    {
        $this->data = $data;
        $this->errors = [];

        foreach ($rules as $field => $ruleSet) {
            $rulesArray = explode('|', $ruleSet);
            $value = $this->data[$field] ?? null;

            foreach ($rulesArray as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return $this;
    }

    private function applyRule($field, $value, $rule)
    {
        $ruleName = $rule;
        $parameters = [];

        if (strpos($rule, ':') !== false) {
            list($ruleName, $paramStr) = explode(':', $rule, 2);
            $parameters = explode(',', $paramStr);
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    $this->addError($field, "The {$this->prettify($field)} field is required.");
                }
                break;
            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "The {$this->prettify($field)} must be a valid email address.");
                }
                break;
            case 'min':
                if (!empty($value) && strlen($value) < $parameters[0]) {
                    $this->addError($field, "The {$this->prettify($field)} must be at least {$parameters[0]} characters.");
                }
                break;
            case 'confirmed':
                if ($value !== ($this->data[$field . '_confirmation'] ?? null)) {
                    $this->addError($field, "The {$this->prettify($field)} confirmation does not match.");
                }
                break;
        }
    }

    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    private function prettify($field)
    {
        return str_replace('_', ' ', $field);
    }

    public function fails()
    {
        return !empty($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }
}