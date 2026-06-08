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
        'contact_information',
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
        'contact_information' => 'array',
    ];

    public function getSiteIconUrlAttribute(): ?string
    {
        return $this->assetUrl($this->site_icon);
    }

    public function getSystemLogoWhiteUrlAttribute(): ?string
    {
        return $this->assetUrl($this->system_logo_white);
    }

    public function getSystemLogoWhitePdfSourceAttribute(): ?string
    {
        if (!$this->system_logo_white) {
            return null;
        }

        $path = public_path('storage/' . $this->system_logo_white);
        if (!file_exists($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension === 'svg') {
            $svg = file_get_contents($path);
            if (preg_match('/<style>(.*?)<\/style>/s', $svg, $matches)) {
                $styleContent = $matches[1];
                preg_match_all('/\.([a-zA-Z0-9_-]+)\s*\{(.*?)\}/s', $styleContent, $classMatches);
                $styles = [];
                for ($i = 0; $i < count($classMatches[1]); $i++) {
                    $className = $classMatches[1][$i];
                    $styleRules = trim($classMatches[2][$i]);
                    $rules = [];
                    foreach (explode(';', $styleRules) as $rule) {
                        if (trim($rule)) {
                            $parts = explode(':', $rule, 2);
                            if (count($parts) === 2) {
                                $rules[trim($parts[0])] = trim($parts[1]);
                            }
                        }
                    }
                    $styles[$className] = $rules;
                }

                $svg = preg_replace_callback('/class=["\']([a-zA-Z0-9_-]+)["\']/', function($m) use ($styles) {
                    $className = $m[1];
                    if (isset($styles[$className])) {
                        $attrs = [];
                        foreach ($styles[$className] as $k => $v) {
                            if (in_array($k, ['fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-miterlimit', 'opacity'])) {
                                $attrs[] = $k . '="' . $v . '"';
                            }
                        }
                        return implode(' ', $attrs);
                    }
                    return $m[0];
                }, $svg);
            }

            $svg = preg_replace('/<\?xml.*?\?>/', '', $svg);
            $svg = preg_replace('/<defs>.*?<\/defs>/s', '', $svg);

            return 'data:image/svg+xml;base64,' . base64_encode(trim($svg));
        }

        $data = file_get_contents($path);
        return 'data:image/' . $extension . ';base64,' . base64_encode($data);
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
