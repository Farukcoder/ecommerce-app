<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'system_name',
        'frontend_website_name',
        'site_motto',
        'hero_badge_text',
        'hero_heading',
        'hero_description',
        'about_hero_heading',
        'about_hero_description',
        'about_mission_heading',
        'about_mission_description',
        'about_mission_image',
        'about_mission_button_text',
        'about_mission_button_url',
        'about_values_heading',
        'about_values_subheading',
        'about_values',
        'about_team_heading',
        'about_team_subheading',
        'about_team_members',
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
        'product_default_image',
    ];

    protected $casts = [
        'about_values' => 'array',
        'about_team_members' => 'array',
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

    public function getProductDefaultImageUrlAttribute(): ?string
    {
        return $this->assetUrl($this->product_default_image);
    }

    public function getAboutMissionImageUrlAttribute(): ?string
    {
        return $this->assetUrl($this->about_mission_image);
    }

    public function getAboutTeamMembersWithUrlsAttribute(): array
    {
        return collect($this->about_team_members ?? [])
            ->map(function (array $member) {
                return [
                    'name' => $member['name'] ?? '',
                    'role' => $member['role'] ?? '',
                    'image' => $member['image'] ?? null,
                    'image_url' => $this->assetUrl($member['image'] ?? null),
                ];
            })
            ->values()
            ->all();
    }

    protected function assetUrl(?string $path): ?string
    {
        return $path ? asset('storage/' . $path) : null;
    }
}
