<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class HeaderSetting extends Model
{
    use Auditable;

    protected $fillable = [
        'header_logo',
        'show_language_switcher',
        'show_currency_switcher',
        'enable_sticky_header',
        'top_header_bg_color',
        'bottom_header_bg_color',
        'top_header_text_color',
        'bottom_header_text_color',
        'help_line_number',
        'header_nav_menu',
    ];

    protected $casts = [
        'show_language_switcher' => 'boolean',
        'show_currency_switcher' => 'boolean',
        'enable_sticky_header' => 'boolean',
        'header_nav_menu' => 'array',
    ];

    public function getHeaderLogoUrlAttribute(): ?string
    {
        return $this->assetUrl($this->header_logo);
    }

    protected function assetUrl(?string $path): ?string
    {
        return $path ? asset('storage/' . $path) : null;
    }
}
