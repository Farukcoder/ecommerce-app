<?php

namespace App\Support;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class CurrencyFormatter
{
    private static ?array $config = null;

    /**
     * Common ISO 4217 currencies for admin presets.
     */
    public const PRESET_CURRENCIES = [
        'BDT' => ['symbol' => '৳', 'name' => 'Bangladeshi Taka'],
        'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
        'EUR' => ['symbol' => '€', 'name' => 'Euro'],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound'],
        'INR' => ['symbol' => '₹', 'name' => 'Indian Rupee'],
        'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar'],
        'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar'],
        'JPY' => ['symbol' => '¥', 'name' => 'Japanese Yen'],
        'CNY' => ['symbol' => '¥', 'name' => 'Chinese Yuan'],
        'SGD' => ['symbol' => 'S$', 'name' => 'Singapore Dollar'],
        'AED' => ['symbol' => 'د.إ', 'name' => 'UAE Dirham'],
        'SAR' => ['symbol' => '﷼', 'name' => 'Saudi Riyal'],
        'MYR' => ['symbol' => 'RM', 'name' => 'Malaysian Ringgit'],
        'THB' => ['symbol' => '฿', 'name' => 'Thai Baht'],
        'PKR' => ['symbol' => '₨', 'name' => 'Pakistani Rupee'],
    ];

    public static function config(): array
    {
        if (static::$config === null) {
            static::$config = Cache::remember('currency_config', 300, function (): array {
                $setting = SystemSetting::query()->latest('id')->first();

                return static::buildConfig($setting);
            });
        }

        return static::$config;
    }

    public static function clearCache(): void
    {
        Cache::forget('currency_config');
        static::$config = null;
    }

    public static function buildConfig(?SystemSetting $setting): array
    {
        $code = strtoupper((string) ($setting?->currency_code ?? 'BDT'));
        $preset = static::PRESET_CURRENCIES[$code] ?? null;

        $available = $setting?->available_currencies ?? [];
        if (! is_array($available) || empty($available)) {
            $available = [
                [
                    'code' => $code,
                    'symbol' => $setting?->currency_symbol ?? ($preset['symbol'] ?? '৳'),
                    'exchange_rate' => 1,
                    'is_default' => true,
                ],
            ];
        }

        return [
            'code' => $code,
            'symbol' => (string) ($setting?->currency_symbol ?? ($preset['symbol'] ?? '৳')),
            'symbol_position' => (string) ($setting?->currency_symbol_position ?? 'before'),
            'decimal_places' => (int) ($setting?->currency_decimal_places ?? 2),
            'thousands_separator' => (string) ($setting?->currency_thousands_separator ?? ','),
            'decimal_separator' => (string) ($setting?->currency_decimal_separator ?? '.'),
            'available' => collect($available)
                ->map(function (array $item): array {
                    return [
                        'code' => strtoupper((string) ($item['code'] ?? '')),
                        'symbol' => (string) ($item['symbol'] ?? ''),
                        'exchange_rate' => (float) ($item['exchange_rate'] ?? 1),
                        'is_default' => (bool) ($item['is_default'] ?? false),
                    ];
                })
                ->filter(fn (array $item) => $item['code'] !== '')
                ->values()
                ->all(),
        ];
    }

    public static function format(float|int|string|null $amount, ?string $currencyCode = null): string
    {
        $config = static::config();
        $amount = (float) $amount;

        $symbol = $config['symbol'];
        $position = $config['symbol_position'];
        $decimals = $config['decimal_places'];
        $thousands = $config['thousands_separator'];
        $decimal = $config['decimal_separator'];

        if ($currencyCode !== null && strtoupper($currencyCode) !== $config['code']) {
            $currency = collect($config['available'])->firstWhere('code', strtoupper($currencyCode));
            if ($currency) {
                $amount = $amount * (float) $currency['exchange_rate'];
                $symbol = (string) $currency['symbol'];
            }
        }

        $formatted = number_format($amount, $decimals, $decimal, $thousands);

        if ($position === 'after') {
            return $formatted . $symbol;
        }

        if ($position === 'before_with_space') {
            return $symbol . ' ' . $formatted;
        }

        return $symbol . $formatted;
    }

    public static function symbol(): string
    {
        return static::config()['symbol'];
    }

    public static function code(): string
    {
        return static::config()['code'];
    }

    public static function toApiArray(): array
    {
        $config = static::config();

        return [
            'code' => $config['code'],
            'symbol' => $config['symbol'],
            'symbol_position' => $config['symbol_position'],
            'decimal_places' => $config['decimal_places'],
            'thousands_separator' => $config['thousands_separator'],
            'decimal_separator' => $config['decimal_separator'],
            'available_currencies' => $config['available'],
        ];
    }
}
