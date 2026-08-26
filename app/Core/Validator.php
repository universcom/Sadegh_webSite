<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Rule-based input validation. Rules are declared as
 * ['email' => 'required|email|max:190'] and messages are resolved through the
 * translation catalogue so errors appear in the visitor's language.
 */
final class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data, array $rules, array $labels = []): self
    {
        $validator = new self($data);
        $validator->validate($rules, $labels);

        return $validator;
    }

    public function validate(array $rules, array $labels = []): void
    {
        foreach ($rules as $field => $ruleset) {
            $value = $this->data[$field] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $label = $labels[$field] ?? Lang::get('field.' . $field);

            foreach (explode('|', $ruleset) as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);

                // Only "required" fires on an empty value; other rules skip it.
                if ($name !== 'required' && ($value === null || $value === '')) {
                    continue;
                }

                $this->apply($name, $field, $value, $arg, $label);

                // Stop at the first failure per field for a cleaner form.
                if (isset($this->errors[$field])) {
                    break;
                }
            }
        }
    }

    private function apply(string $rule, string $field, mixed $value, ?string $arg, string $label): void
    {
        switch ($rule) {
            case 'required':
                if ($value === null || $value === '' || (is_array($value) && $value === [])) {
                    $this->addError($field, 'validation.required', ['field' => $label]);
                }
                break;

            case 'email':
                if (!filter_var((string) $value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'validation.email', ['field' => $label]);
                }
                break;

            case 'url':
                if (!filter_var((string) $value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, 'validation.url', ['field' => $label]);
                }
                break;

            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, 'validation.numeric', ['field' => $label]);
                }
                break;

            case 'integer':
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, 'validation.numeric', ['field' => $label]);
                }
                break;

            case 'min':
                if (mb_strlen((string) $value) < (int) $arg) {
                    $this->addError($field, 'validation.min', ['field' => $label, 'min' => (int) $arg]);
                }
                break;

            case 'max':
                if (mb_strlen((string) $value) > (int) $arg) {
                    $this->addError($field, 'validation.max', ['field' => $label, 'max' => (int) $arg]);
                }
                break;

            case 'in':
                if (!in_array((string) $value, explode(',', (string) $arg), true)) {
                    $this->addError($field, 'validation.invalid', ['field' => $label]);
                }
                break;

            case 'slug':
                if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', (string) $value)) {
                    $this->addError($field, 'validation.slug', ['field' => $label]);
                }
                break;

            case 'phone':
                // Digits, spaces and the usual separators; 7–20 digits overall.
                $digits = preg_replace('/\D+/', '', (string) $value) ?? '';
                if (!preg_match('/^[0-9 ()+\-\.]{7,25}$/', (string) $value) || strlen($digits) < 7 || strlen($digits) > 20) {
                    $this->addError($field, 'validation.phone', ['field' => $label]);
                }
                break;

            case 'confirmed':
                if ((string) $value !== (string) ($this->data[$field . '_confirmation'] ?? '')) {
                    $this->addError($field, 'validation.confirmed', ['field' => $label]);
                }
                break;

            case 'no_urls':
                // Cheap spam control for free-text fields.
                if (preg_match('#(https?://|www\.|\[url=)#i', (string) $value)) {
                    $this->addError($field, 'validation.no_urls', ['field' => $label]);
                }
                break;
        }
    }

    public function addError(string $field, string $key, array $replace = []): void
    {
        // Numeric placeholders are localised so a Persian or Arabic message does
        // not mix Latin digits into otherwise RTL text.
        foreach ($replace as $name => $value) {
            if (is_int($value) || (is_string($value) && ctype_digit($value))) {
                $replace[$name] = Lang::digits((string) $value);
            }
        }

        $this->errors[$field] ??= Lang::get($key, $replace);
    }

    public function fails(): bool
    {
        return $this->errors !== [];
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function first(): ?string
    {
        return $this->errors === [] ? null : reset($this->errors);
    }
}
