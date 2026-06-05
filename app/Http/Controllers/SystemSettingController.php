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

        $systemSetting = new SystemSetting();
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
        ]);

        $fileFields = [
            'site_icon' => 'settings/site-icon',
            'system_logo_white' => 'settings/logos',
            'system_logo_black' => 'settings/logos',
            'flash_deal_page_banner_large' => 'settings/banners',
            'flash_deal_page_banner_small' => 'settings/banners',
            'product_default_image' => 'settings/products',
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

        return $validated;
    }

    protected function defaultAttributes(): array
    {
        return [
            'system_name' => 'Active eCommerce CMS',
            'frontend_website_name' => 'Active eCommerce',
            'site_motto' => 'Demo of Active eCommerce CMS',
            'uploaded_image_format' => 'webp',
            'website_base_color' => '#0080FF',
            'website_base_hover_color' => '#0066CC',
            'website_secondary_base_color' => '#171717',
            'website_secondary_base_hover_color' => '#5D5D62',
        ];
    }
}
