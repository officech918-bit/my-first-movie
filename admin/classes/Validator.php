<?php

class Validator
{
    private array $errors = [];
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sanitize(): void
    {
        foreach ($this->data as $key => $value) {
            if (is_string($value)) {
                $this->data[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
    }

    public function getData(): array
    {
        return $this->data;
    }


    public function validate(array $rules): bool
    {
        foreach ($rules as $field => $ruleSet) {
            $value = $this->data[$field] ?? null;

            foreach ($ruleSet as $rule) {
                $ruleName = $rule;
                $ruleValue = null;

                if (strpos($rule, ':') !== false) {
                    [$ruleName, $ruleValue] = explode(':', $rule, 2);
                }

                $methodName = 'validate' . ucfirst($ruleName);

                if (method_exists($this, $methodName)) {
                    $this->$methodName($field, $value, $ruleValue);
                }
            }
        }

        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    private function validateRequired($field, $value, $param): void
    {
        if (empty($value)) {
            $this->addError($field, "The {$field} field is required.");
        }
    }

    private function validateMinLength($field, $value, $param): void
    {
        if (strlen($value) < $param) {
            $this->addError($field, "The {$field} must be at least {$param} characters.");
        }
    }

    private function validateMaxLength($field, $value, $param): void
    {
        if (strlen($value) > $param) {
            $this->addError($field, "The {$field} may not be greater than {$param} characters.");
        }
    }

    private function validateAlpha($field, $value, $param): void
    {
        if (!preg_match('/^[a-zA-Z]+$/', $value)) {
            $this->addError($field, "The {$field} may only contain letters.");
        }
    }

    private function validateAlphaNum($field, $value, $param): void
    {
        if (!preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            $this->addError($field, "The {$field} may only contain letters and numbers.");
        }
    }

    private function validateDate($field, $value, $param): void
    {
        if (!strtotime($value)) {
            $this->addError($field, "The {$field} is not a valid date.");
        }
    }
}