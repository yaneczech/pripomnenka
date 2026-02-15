<?php
/**
 * Připomněnka - Validator Helper
 *
 * Validace vstupních dat
 */

declare(strict_types=1);

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Statická tovární metoda
     */
    public static function make(array $data): self
    {
        return new self($data);
    }

    /**
     * Validace povinného pole
     */
    public function required(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;

        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->errors[$field] = $message ?? "Pole {$field} je povinné.";
        }

        return $this;
    }

    /**
     * Validace emailu
     */
    public function email(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message ?? 'Neplatný formát emailu.';
        }

        return $this;
    }

    /**
     * Validace telefonního čísla
     */
    public function phone(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if ($value !== '' && !is_valid_phone($value)) {
            $this->errors[$field] = $message ?? 'Neplatný formát telefonního čísla.';
        }

        return $this;
    }

    /**
     * Validace minimální délky
     */
    public function minLength(string $field, int $min, ?string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if ($value !== '' && mb_strlen($value) < $min) {
            $this->errors[$field] = $message ?? "Pole musí mít alespoň {$min} znaků.";
        }

        return $this;
    }

    /**
     * Validace maximální délky
     */
    public function maxLength(string $field, int $max, ?string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if (mb_strlen($value) > $max) {
            $this->errors[$field] = $message ?? "Pole může mít maximálně {$max} znaků.";
        }

        return $this;
    }

    /**
     * Validace hodnoty z výčtu
     */
    public function in(string $field, array $allowed, ?string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = $message ?? 'Neplatná hodnota.';
        }

        return $this;
    }

    /**
     * Validace číselného rozsahu
     */
    public function between(string $field, int $min, int $max, ?string $message = null): self
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && $value !== '') {
            $intValue = (int) $value;
            if ($intValue < $min || $intValue > $max) {
                $this->errors[$field] = $message ?? "Hodnota musí být mezi {$min} a {$max}.";
            }
        }

        return $this;
    }

    /**
     * Validace data (den a měsíc)
     */
    public function validDate(string $dayField, string $monthField, ?string $message = null): self
    {
        $day = (int) ($this->data[$dayField] ?? 0);
        $month = (int) ($this->data[$monthField] ?? 0);

        if ($day < 1 || $day > 31 || $month < 1 || $month > 12) {
            $this->errors[$dayField] = $message ?? 'Neplatné datum.';
            return $this;
        }

        // Kontrola platnosti dne pro daný měsíc
        $daysInMonth = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        if ($day > $daysInMonth[$month - 1]) {
            $this->errors[$dayField] = $message ?? 'Neplatné datum.';
        }

        return $this;
    }

    /**
     * Validace hesla (síla)
     */
    public function password(string $field, ?string $message = null): self
    {
        $value = $this->data[$field] ?? '';

        if ($value !== '' && mb_strlen($value) < 8) {
            $this->errors[$field] = $message ?? 'Heslo musí mít alespoň 8 znaků.';
        }

        return $this;
    }

    /**
     * Validace shody dvou polí
     */
    public function matches(string $field, string $otherField, ?string $message = null): self
    {
        $value = $this->data[$field] ?? '';
        $otherValue = $this->data[$otherField] ?? '';

        if ($value !== $otherValue) {
            $this->errors[$field] = $message ?? 'Hodnoty se neshodují.';
        }

        return $this;
    }

    /**
     * Vlastní validační pravidlo
     */
    public function custom(string $field, callable $callback, string $message): self
    {
        $value = $this->data[$field] ?? null;

        if (!$callback($value, $this->data)) {
            $this->errors[$field] = $message;
        }

        return $this;
    }

    /**
     * Přidání chyby ručně
     */
    public function addError(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }

    /**
     * Kontrola validity
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * Kontrola neplatnosti
     */
    public function fails(): bool
    {
        return !$this->isValid();
    }

    /**
     * Získání chyb
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Získání první chyby pro pole
     */
    public function error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    /**
     * Získání validovaných dat
     */
    public function validated(): array
    {
        $validated = [];

        foreach (array_keys($this->data) as $key) {
            if (!isset($this->errors[$key])) {
                $validated[$key] = $this->data[$key];
            }
        }

        return $validated;
    }

    /**
     * Sanitizace hodnoty
     */
    public static function sanitize(mixed $value): mixed
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_array($value)) {
            return array_map([self::class, 'sanitize'], $value);
        }

        return $value;
    }

    /**
     * Sanitizace emailu
     */
    public static function sanitizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    /**
     * Sanitizace telefonního čísla
     */
    public static function sanitizePhone(string $phone): string
    {
        return format_phone($phone);
    }
}
