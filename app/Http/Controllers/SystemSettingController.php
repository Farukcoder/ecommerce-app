<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SystemSettingController extends Controller
{
    public function index()
    {
        $systemSetting = SystemSetting::query()->latest('id')->first();

        return view('system-settings.index', compact('systemSetting'));
    }

    public function create()
    {
        $systemSetting = new SystemSetting($this->defaultAttributes());

        if (SystemSetting::query()->exists()) {
            return redirect()->route('system-settings.index');
        }

        return view('system-settings.create', compact('systemSetting'));
    }

    public function store(Request $request)
    {
        if (SystemSetting::query()->exists()) {
            return redirect()->route('system-settings.index')->with('success', 'System setting already exists. Use edit to update it.');
        }

        $systemSetting = new SystemSetting;
        $systemSetting->fill($this->validatedAttributes($request));
        $systemSetting->save();

        return redirect()->route('system-settings.index')->with('success', 'System setting created successfully.');
    }

    public function edit(SystemSetting $systemSetting)
    {
        return view('system-settings.edit', compact('systemSetting'));
    }

    public function update(Request $request, SystemSetting $systemSetting)
    {
        $systemSetting->fill($this->validatedAttributes($request, $systemSetting));
        $systemSetting->save();

        return redirect()->route('system-settings.index')->with('success', 'System setting updated successfully.');
    }

    protected function validatedAttributes(Request $request, ?SystemSetting $systemSetting = null): array
    {
        $validated = $request->validate([
            'system_name' => ['required', 'string', 'max:255'],
            'frontend_website_name' => ['required', 'string', 'max:255'],
            'site_motto' => ['nullable', 'string', 'max:255'],
            'hero_badge_text' => ['nullable', 'string', 'max:255'],
            'hero_heading' => ['nullable', 'string', 'max:255'],
            'hero_description' => ['nullable', 'string', 'max:1000'],
            'about_hero_heading' => ['nullable', 'string', 'max:255'],
            'about_hero_description' => ['nullable', 'string', 'max:2000'],
            'about_mission_heading' => ['nullable', 'string', 'max:255'],
            'about_mission_description' => ['nullable', 'string', 'max:5000'],
            'about_mission_button_text' => ['nullable', 'string', 'max:100'],
            'about_mission_button_url' => ['nullable', 'string', 'max:255'],
            'about_mission_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:4096'],
            'about_values_heading' => ['nullable', 'string', 'max:255'],
            'about_values_subheading' => ['nullable', 'string', 'max:255'],
            'about_values' => ['nullable', 'array'],
            'about_values.*.title' => ['nullable', 'string', 'max:255'],
            'about_values.*.description' => ['nullable', 'string', 'max:1000'],
            'about_team_heading' => ['nullable', 'string', 'max:255'],
            'about_team_subheading' => ['nullable', 'string', 'max:255'],
            'about_team_members' => ['nullable', 'array'],
            'about_team_members.*.name' => ['nullable', 'string', 'max:255'],
            'about_team_members.*.role' => ['nullable', 'string', 'max:255'],
            'about_team_members.*.image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:4096'],
            'contact_information' => ['nullable', 'array'],
            'contact_information.*.icon' => ['nullable', 'string', Rule::in(['email', 'phone', 'map-pin', 'clock'])],
            'contact_information.*.title' => ['nullable', 'string', 'max:255'],
            'contact_information.*.details' => ['nullable', 'string', 'max:2000'],
            'uploaded_image_format' => ['required', Rule::in(['webp', 'jpg', 'jpeg', 'png', 'svg'])],
            'website_base_color' => ['required', 'string', 'max:20'],
            'website_base_hover_color' => ['required', 'string', 'max:20'],
            'website_secondary_base_color' => ['required', 'string', 'max:20'],
            'website_secondary_base_hover_color' => ['required', 'string', 'max:20'],
            'site_icon' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:4096'],
            'system_logo_white' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:4096'],
            'system_logo_black' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:4096'],
            'flash_deal_page_banner_large' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:4096'],
            'flash_deal_page_banner_small' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:4096'],
            'product_default_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:4096'],
            'currency_code' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'currency_symbol_position' => ['required', Rule::in(['before', 'after', 'before_with_space'])],
            'currency_decimal_places' => ['required', 'integer', 'min:0', 'max:4'],
            'currency_thousands_separator' => ['required', 'string', 'max:5'],
            'currency_decimal_separator' => ['required', 'string', 'max:5'],
            'available_currencies' => ['nullable', 'array'],
            'available_currencies.*.code' => ['nullable', 'string', 'max:10'],
            'available_currencies.*.symbol' => ['nullable', 'string', 'max:10'],
            'available_currencies.*.exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'default_locale' => ['required', 'string', 'max:10'],
            'available_locales' => ['nullable', 'array'],
            'available_locales.*.code' => ['nullable', 'string', 'max:10'],
            'available_locales.*.name' => ['nullable', 'string', 'max:255'],
        ]);

        $fileFields = [
            'site_icon' => 'settings/site-icon',
            'system_logo_white' => 'settings/logos',
            'system_logo_black' => 'settings/logos',
            'flash_deal_page_banner_large' => 'settings/banners',
            'flash_deal_page_banner_small' => 'settings/banners',
            'product_default_image' => 'settings/products',
            'about_mission_image' => 'settings/about',
        ];

        foreach ($fileFields as $field => $directory) {
            if ($request->hasFile($field)) {
                if ($systemSetting?->{$field}) {
                    Storage::disk('public')->delete($systemSetting->{$field});
                }

                $validated[$field] = $request->file($field)->store($directory, 'public');

                continue;
            }

            if ($systemSetting) {
                $validated[$field] = $systemSetting->{$field};
            }
        }

        $validated['about_values'] = collect($request->input('about_values', []))
            ->map(function ($item) {
                return [
                    'title' => trim((string) ($item['title'] ?? '')),
                    'description' => trim((string) ($item['description'] ?? '')),
                ];
            })
            ->filter(fn ($item) => $item['title'] !== '' || $item['description'] !== '')
            ->values()
            ->all();

        $teamMembers = [];

        foreach ($request->input('about_team_members', []) as $index => $member) {
            $item = [
                'name' => trim((string) ($member['name'] ?? '')),
                'role' => trim((string) ($member['role'] ?? '')),
                'image' => $member['existing_image'] ?? null,
            ];

            if ($request->hasFile("about_team_members.{$index}.image")) {
                if ($item['image']) {
                    Storage::disk('public')->delete($item['image']);
                }

                $item['image'] = $request->file("about_team_members.{$index}.image")
                    ->store('settings/about/team', 'public');
            }

            if ($item['name'] !== '' || $item['role'] !== '' || $item['image']) {
                $teamMembers[] = $item;
            }
        }

        $validated['about_team_members'] = $teamMembers;

        $validated['contact_information'] = collect($request->input('contact_information', []))
            ->map(function ($item) {
                $details = collect(preg_split('/\r\n|\r|\n/', (string) ($item['details'] ?? '')))
                    ->map(fn ($line) => trim($line))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'icon' => trim((string) ($item['icon'] ?? 'email')),
                    'title' => trim((string) ($item['title'] ?? '')),
                    'details' => $details,
                ];
            })
            ->filter(fn ($item) => $item['title'] !== '' || ! empty($item['details']))
            ->values()
            ->all();

        $defaultCode = strtoupper((string) $validated['currency_code']);
        $validated['currency_code'] = $defaultCode;

        $validated['available_currencies'] = collect($request->input('available_currencies', []))
            ->map(function (array $item) use ($defaultCode) {
                return [
                    'code' => strtoupper(trim((string) ($item['code'] ?? ''))),
                    'symbol' => trim((string) ($item['symbol'] ?? '')),
                    'exchange_rate' => (float) ($item['exchange_rate'] ?? 1),
                    'is_default' => strtoupper(trim((string) ($item['code'] ?? ''))) === $defaultCode,
                ];
            })
            ->filter(fn (array $item) => $item['code'] !== '')
            ->values()
            ->all();

        if (empty($validated['available_currencies'])) {
            $validated['available_currencies'] = [
                [
                    'code' => $defaultCode,
                    'symbol' => $validated['currency_symbol'],
                    'exchange_rate' => 1,
                    'is_default' => true,
                ],
            ];
        }

        $defaultLocale = strtolower((string) $validated['default_locale']);
        $validated['default_locale'] = $defaultLocale;

        $validated['available_locales'] = collect($request->input('available_locales', []))
            ->map(function (array $item) use ($defaultLocale) {
                return [
                    'code' => strtolower(trim((string) ($item['code'] ?? ''))),
                    'name' => trim((string) ($item['name'] ?? '')),
                    'is_default' => strtolower(trim((string) ($item['code'] ?? ''))) === $defaultLocale,
                ];
            })
            ->filter(fn (array $item) => $item['code'] !== '')
            ->values()
            ->all();

        if (empty($validated['available_locales'])) {
            $validated['available_locales'] = [
                [
                    'code' => $defaultLocale,
                    'name' => $defaultLocale === 'bn' ? 'Bangla' : 'English',
                    'is_default' => true,
                ],
            ];
        }

        return $validated;
    }

    protected function defaultAttributes(): array
    {
        return [
            'system_name' => 'Active eCommerce CMS',
            'frontend_website_name' => 'Active eCommerce',
            'site_motto' => 'Demo of Active eCommerce CMS',
            'hero_badge_text' => 'New Season Collection',
            'hero_heading' => 'Discover Your Perfect Style',
            'hero_description' => 'Curated collections of premium products designed for the modern lifestyle. Quality meets elegance.',
            'about_hero_heading' => 'Our Story',
            'about_hero_description' => 'Founded in 2020, Nityodinar Kutir was born from a passion for quality craftsmanship and timeless design. We believe that exceptional products should be accessible to everyone.',
            'about_mission_heading' => 'Our Mission',
            'about_mission_description' => "At Nityodinar Kutir, we're committed to delivering products that combine quality, style, and value. Every item in our collection is carefully selected to meet the highest standards.\n\nWe believe in creating a shopping experience that's as exceptional as the products we offer. From curated collections to personalized service, we're here to help you discover pieces you'll love.",
            'about_mission_button_text' => 'Explore Our Collection',
            'about_mission_button_url' => '/shop',
            'about_values_heading' => 'Our Values',
            'about_values_subheading' => 'The principles that guide everything we do',
            'about_values' => [
                ['title' => 'Quality First', 'description' => 'We never compromise on quality. Every product is carefully vetted to ensure it meets our high standards.'],
                ['title' => 'Sustainable Practice', 'description' => 'We partner with ethical manufacturers and prioritize sustainable materials in our collections.'],
                ['title' => 'Customer Focus', 'description' => 'Your satisfaction is our priority. We listen, adapt, and go the extra mile to exceed expectations.'],
                ['title' => 'Innovation', 'description' => 'We continuously explore new trends and technologies to bring you the best products and shopping experience.'],
            ],
            'about_team_heading' => 'Meet Our Team',
            'about_team_subheading' => 'The passionate people behind Nityodinar Kutir',
            'about_team_members' => [
                ['name' => 'Sarah Chen', 'role' => 'Founder & CEO', 'image' => null],
                ['name' => 'Marcus Johnson', 'role' => 'Creative Director', 'image' => null],
                ['name' => 'Emily Rodriguez', 'role' => 'Head of Operations', 'image' => null],
                ['name' => 'David Park', 'role' => 'Lead Designer', 'image' => null],
            ],
            'contact_information' => [
                ['icon' => 'email', 'title' => 'Email', 'details' => ['support@luxe.com', 'sales@luxe.com']],
                ['icon' => 'phone', 'title' => 'Phone', 'details' => ['+1 (555) 123-4567', 'Mon-Fri 9AM-6PM EST']],
                ['icon' => 'map-pin', 'title' => 'Address', 'details' => ['123 Fashion Avenue', 'New York, NY 10001']],
                ['icon' => 'clock', 'title' => 'Business Hours', 'details' => ['Monday - Friday: 9AM - 6PM', 'Saturday: 10AM - 4PM', 'Sunday: Closed']],
            ],
            'uploaded_image_format' => 'webp',
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
            'available_currencies' => [
                ['code' => 'BDT', 'symbol' => '৳', 'exchange_rate' => 1, 'is_default' => true],
            ],
            'default_locale' => 'en',
            'available_locales' => [
                ['code' => 'en', 'name' => 'English', 'is_default' => true],
                ['code' => 'bn', 'name' => 'Bangla', 'is_default' => false],
            ],
        ];
    }
}
