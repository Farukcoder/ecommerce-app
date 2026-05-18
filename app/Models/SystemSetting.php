<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'system_name',
        'frontend_website_name',
        'site_motto',
        'site_icon',
        'system_logo_white',
        'system_logo_black',
        'uploaded_image_format',
        'website_base_color',
        'website_base_hover_color',
        'website_secondary_base_color',
        'website_secondary_base_hover_color',
        'flash_deal_page_banner_large',
        'flash_deal_page_banner_small',
    ];

    public function getSiteIconUrlAttribute(): ?string
    {
        return $this->assetUrl($this->site_icon);
    }

    public function getSystemLogoWhiteUrlAttribute(): ?string
    {
        return $this->assetUrl($this->system_logo_white);
    }

    public function getSystemLogoBlackUrlAttribute(): ?string
    {
        return $this->assetUrl($this->system_logo_black);
    }

    public function getFlashDealPageBannerLargeUrlAttribute(): ?string
    {
        return $this->assetUrl($this->flash_deal_page_banner_large);
    }

    public function getFlashDealPageBannerSmallUrlAttribute(): ?string
    {
        return $this->assetUrl($this->flash_deal_page_banner_small);
    }

    protected function assetUrl(?string $path): ?string
    {
        return $path ? asset('storage/' . $path) : null;
    }
}
