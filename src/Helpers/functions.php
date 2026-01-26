<?php
/**
 * Připomněnka - Globální pomocné funkce
 */

declare(strict_types=1);

/**
 * Bezpečný výstup textu (ochrana proti XSS)
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Přesměrování na jinou URL
 */
function redirect(string $url, int $code = 302): never
{
    header("Location: {$url}", true, $code);
    exit;
}

/**
 * Získání hodnoty z pole s výchozí hodnotou
 */
function array_get(array $array, string $key, mixed $default = null): mixed
{
    return $array[$key] ?? $default;
}

/**
 * Vygenerování náhodného tokenu
 */
function generate_token(int $length = 32): string
{
    return bin2hex(random_bytes($length));
}

/**
 * Vygenerování 6místného OTP kódu
 */
function generate_otp(): string
{
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Formátování telefonního čísla
 */
function format_phone(string $phone): string
{
    // Odstranění všeho kromě číslic a +
    $phone = preg_replace('/[^\d+]/', '', $phone);

    // Přidání +420 pokud chybí předvolba
    if (!str_starts_with($phone, '+')) {
        if (str_starts_with($phone, '00')) {
            $phone = '+' . substr($phone, 2);
        } elseif (strlen($phone) === 9) {
            $phone = '+420' . $phone;
        }
    }

    return $phone;
}

/**
 * Formátování data v češtině
 */
function format_date(string $date, string $format = 'j. n. Y'): string
{
    $dt = new DateTime($date);
    return $dt->format($format);
}

/**
 * Formátování data s názvem měsíce
 */
function format_date_long(int $day, int $month): string
{
    $months = [
        1 => 'ledna', 2 => 'února', 3 => 'března', 4 => 'dubna',
        5 => 'května', 6 => 'června', 7 => 'července', 8 => 'srpna',
        9 => 'září', 10 => 'října', 11 => 'listopadu', 12 => 'prosince'
    ];

    return "{$day}. {$months[$month]}";
}

/**
 * Výpočet počtu dní do události
 */
function days_until(int $day, int $month): int
{
    $now = new DateTime();
    $year = (int) $now->format('Y');

    $event = new DateTime("{$year}-{$month}-{$day}");

    // Pokud už letos proběhlo, počítat do příštího roku
    if ($event < $now) {
        $event->modify('+1 year');
    }

    return (int) $now->diff($event)->days;
}

/**
 * Překlad typu události
 */
function translate_event_type(string $type): string
{
    $types = [
        'birthday' => 'Narozeniny',
        'nameday' => 'Svátek',
        'wedding_anniversary' => 'Výročí svatby',
        'relationship_anniversary' => 'Výročí vztahu',
        'mothers_day' => 'Den matek',
        'fathers_day' => 'Den otců',
        'valentines' => 'Valentýn',
        'other' => 'Jiné',
    ];

    return $types[$type] ?? $type;
}

/**
 * Překlad vztahu
 */
function translate_relation(string $relation): string
{
    $relations = [
        'wife' => 'Manželka',
        'husband' => 'Manžel',
        'mother' => 'Matka',
        'father' => 'Otec',
        'daughter' => 'Dcera',
        'son' => 'Syn',
        'grandmother' => 'Babička',
        'grandfather' => 'Dědeček',
        'sister' => 'Sestra',
        'brother' => 'Bratr',
        'mother_in_law' => 'Tchyně',
        'father_in_law' => 'Tchán',
        'partner' => 'Partner/ka',
        'friend' => 'Kamarád/ka',
        'colleague' => 'Kolega/yně',
        'other' => 'Jiné',
    ];

    return $relations[$relation] ?? $relation;
}

/**
 * Překlad cenového rozsahu
 */
function translate_price_range(string $range): string
{
    $ranges = [
        'under_500' => 'Do 500 Kč',
        '500_800' => '500–800 Kč',
        '800_1200' => '800–1200 Kč',
        '1200_2000' => '1200–2000 Kč',
        'over_2000' => 'Nad 2000 Kč',
        'to_discuss' => 'Poradíme při hovoru',
    ];

    return $ranges[$range] ?? $range;
}

/**
 * Validace emailu
 */
function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validace telefonního čísla (CZ formát)
 */
function is_valid_phone(string $phone): bool
{
    $phone = preg_replace('/[^\d+]/', '', $phone);
    // Akceptuje: +420XXXXXXXXX, 00420XXXXXXXXX, XXXXXXXXX
    return (bool) preg_match('/^(\+420|00420)?[1-9]\d{8}$/', $phone);
}

/**
 * Hash pro vyhledávání (email/telefon)
 */
function create_search_hash(string $value): string
{
    return hash('sha256', strtolower(trim($value)));
}

/**
 * Načtení view souboru
 */
function view(string $name, array $data = [], ?string $layout = null): void
{
    extract($data);

    $viewPath = ROOT_PATH . '/src/Views/' . str_replace('.', '/', $name) . '.php';

    if (!file_exists($viewPath)) {
        throw new RuntimeException("View not found: {$name}");
    }

    if ($layout) {
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $layoutPath = ROOT_PATH . '/src/Views/layouts/' . $layout . '.php';
        if (!file_exists($layoutPath)) {
            throw new RuntimeException("Layout not found: {$layout}");
        }
        require $layoutPath;
    } else {
        require $viewPath;
    }
}

/**
 * Flash zprávy
 */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][$type][] = $message;
}

function get_flash(): array
{
    $flash = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flash;
}

function has_flash(): bool
{
    return !empty($_SESSION['flash']);
}

/**
 * Staré hodnoty z formuláře
 */
function old(string $key, mixed $default = ''): mixed
{
    return $_SESSION['old_input'][$key] ?? $default;
}

function set_old_input(array $data): void
{
    $_SESSION['old_input'] = $data;
}

function clear_old_input(): void
{
    unset($_SESSION['old_input']);
}

/**
 * Asset URL s verzováním
 */
function asset(string $path): string
{
    $fullPath = PUBLIC_PATH . '/assets/' . ltrim($path, '/');
    $version = file_exists($fullPath) ? filemtime($fullPath) : time();
    return '/assets/' . ltrim($path, '/') . '?v=' . $version;
}
