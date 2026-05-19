<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;

class SystemSettingController extends Controller
{
    /**
     * Return the latest system setting for storefront.
     */
    public function show(): JsonResponse
    {
        $setting = SystemSetting::query()->latest('id')->first();

        if (!$setting) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'id' => $setting->id,
                'system_name' => $setting->system_name,
                'frontend_website_name' => $setting->frontend_website_name,
                'site_motto' => $setting->site_motto,
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
                'site_icon_url' => $setting->site_icon_url,
                'system_logo_white_url' => $setting->system_logo_white_url,
                'system_logo_black_url' => $setting->system_logo_black_url,
                'flash_deal_page_banner_large_url' => $setting->flash_deal_page_banner_large_url,
                'flash_deal_page_banner_small_url' => $setting->flash_deal_page_banner_small_url,
                'created_at' => $setting->created_at?->toISOString(),
                'updated_at' => $setting->updated_at?->toISOString(),
            ],
        ]);
    }
}
