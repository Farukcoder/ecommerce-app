<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_system_settings_supports_locale_fields(): void
    {
        $setting = SystemSetting::create([
            'system_name' => 'Test CMS',
            'frontend_website_name' => 'Test Web',
            'default_locale' => 'bn',
            'available_locales' => [
                ['code' => 'en', 'name' => 'English', 'is_default' => false],
                ['code' => 'bn', 'name' => 'Bangla', 'is_default' => true],
            ],
            'website_base_color' => '#0080FF',
            'website_base_hover_color' => '#0066CC',
            'website_secondary_base_color' => '#171717',
            'website_secondary_base_hover_color' => '#5D5D62',
            'currency_code' => 'BDT',
            'currency_symbol' => '৳',
            'currency_symbol_position' => 'before',
            'currency_decimal_places' => 2,
            'currency_thousands_separator' => ',',
            'currency_decimal_separator' => '.',
        ]);

        $this->assertEquals('bn', $setting->default_locale);
        $this->assertIsArray($setting->available_locales);
        $this->assertEquals('bn', $setting->available_locales[1]['code']);
    }

    public function test_set_locale_via_query_parameter(): void
    {
        $this->get('/?lang=bn');
        $this->assertEquals('bn', App::getLocale());

        $this->get('/?lang=en');
        $this->assertEquals('en', App::getLocale());
    }

    public function test_set_locale_via_header(): void
    {
        $this->get('/', ['X-Locale' => 'bn']);
        $this->assertEquals('bn', App::getLocale());

        $this->get('/', ['X-Locale' => 'en']);
        $this->assertEquals('en', App::getLocale());
    }

    public function test_set_locale_via_session(): void
    {
        $this->withSession(['locale' => 'bn'])->get('/');
        $this->assertEquals('bn', App::getLocale());
    }

    public function test_system_setting_api_returns_localization_settings(): void
    {
        SystemSetting::create([
            'system_name' => 'Test CMS',
            'frontend_website_name' => 'Test Web',
            'default_locale' => 'bn',
            'available_locales' => [
                ['code' => 'en', 'name' => 'English', 'is_default' => false],
                ['code' => 'bn', 'name' => 'Bangla', 'is_default' => true],
            ],
            'website_base_color' => '#0080FF',
            'website_base_hover_color' => '#0066CC',
            'website_secondary_base_color' => '#171717',
            'website_secondary_base_hover_color' => '#5D5D62',
            'currency_code' => 'BDT',
            'currency_symbol' => '৳',
            'currency_symbol_position' => 'before',
            'currency_decimal_places' => 2,
            'currency_thousands_separator' => ',',
            'currency_decimal_separator' => '.',
        ]);

        $response = $this->getJson('/api/home/system-settings');
        $response->assertStatus(200)
            ->assertJsonPath('data.default_locale', 'bn')
            ->assertJsonPath('data.available_locales.1.code', 'bn');
    }

    public function test_resolves_localized_static_texts(): void
    {
        App::setLocale('en');
        $this->assertEquals('Products', __('messages.products'));

        App::setLocale('bn');
        $this->assertEquals('পণ্যসমূহ', __('messages.products'));
    }
}
