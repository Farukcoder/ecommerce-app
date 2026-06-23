<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HeaderSetting;
use App\Models\SystemSetting;
use App\Support\CurrencyFormatter;
use Illuminate\Http\JsonResponse;

class SystemSettingController extends Controller
{
    /**
     * Return the latest system setting for storefront.
     */
    public function show(): JsonResponse
    {
        $setting = SystemSetting::query()->latest('id')->first();
        $headerSetting = HeaderSetting::query()->latest('id')->first();

        if (! $setting) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id' => $setting->id,
                'system_name' => $setting->system_name,
                'frontend_website_name' => $setting->frontend_website_name,
                'site_motto' => $setting->site_motto,
                'hero_badge_text' => $setting->hero_badge_text,
                'hero_heading' => $setting->hero_heading,
                'hero_description' => $setting->hero_description,
                'about_hero_heading' => $setting->about_hero_heading,
                'about_hero_description' => $setting->about_hero_description,
                'about_mission_heading' => $setting->about_mission_heading,
                'about_mission_description' => $setting->about_mission_description,
                'about_mission_image' => $setting->about_mission_image,
                'about_mission_button_text' => $setting->about_mission_button_text,
                'about_mission_button_url' => $setting->about_mission_button_url,
                'about_values_heading' => $setting->about_values_heading,
                'about_values_subheading' => $setting->about_values_subheading,
                'about_values' => $setting->about_values ?? [],
                'about_team_heading' => $setting->about_team_heading,
                'about_team_subheading' => $setting->about_team_subheading,
                'about_team_members' => $setting->about_team_members_with_urls,
                'contact_information' => $setting->contact_information ?? [],
                'about_mission_image_url' => $setting->about_mission_image_url,
                'uploaded_image_format' => $setting->uploaded_image_format,
                'website_base_color' => $setting->website_base_color,
                'website_base_hover_color' => $setting->website_base_hover_color,
                'website_secondary_base_color' => $setting->website_secondary_base_color,
                'website_secondary_base_hover_color' => $setting->website_secondary_base_hover_color,
                'site_icon' => $setting->site_icon,
                'system_logo_white' => $setting->system_logo_white,
                'system_logo_black' => $setting->system_logo_black,
                'flash_deal_page_banner_large' => $setting->flash_deal_page_banner_large,
                'flash_deal_page_banner_small' => $setting->flash_deal_page_banner_small,
                'product_default_image' => $setting->product_default_image,
                'site_icon_url' => $setting->site_icon_url,
                'system_logo_white_url' => $setting->system_logo_white_url,
                'system_logo_black_url' => $setting->system_logo_black_url,
                'flash_deal_page_banner_large_url' => $setting->flash_deal_page_banner_large_url,
                'flash_deal_page_banner_small_url' => $setting->flash_deal_page_banner_small_url,
                'product_default_image_url' => $setting->product_default_image_url,
                'currency' => CurrencyFormatter::toApiArray(),
                'show_currency_switcher' => (bool) ($headerSetting?->show_currency_switcher ?? true),
                'default_locale' => $setting->default_locale ?? 'en',
                'available_locales' => $setting->available_locales ?? [
                    ['code' => 'en', 'name' => 'English', 'is_default' => true],
                    ['code' => 'bn', 'name' => 'Bangla', 'is_default' => false],
                ],
                'created_at' => $setting->created_at?->toISOString(),
                'updated_at' => $setting->updated_at?->toISOString(),
            ],
        ]);
    }
}
