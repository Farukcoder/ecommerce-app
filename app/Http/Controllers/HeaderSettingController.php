<?php

namespace App\Http\Controllers;

use App\Models\HeaderSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeaderSettingController extends Controller
{
    public function index()
    {
        $headerSetting = HeaderSetting::query()->latest('id')->first();

        return view('header-settings.index', compact('headerSetting'));
    }

    public function create()
    {
        if (HeaderSetting::query()->exists()) {
            return redirect()->route('header-settings.index');
        }

        $headerSetting = new HeaderSetting($this->defaultAttributes());

        return view('header-settings.create', compact('headerSetting'));
    }

    public function store(Request $request)
    {
        if (HeaderSetting::query()->exists()) {
            return redirect()->route('header-settings.index')->with('success', 'Header setting already exists. Use edit to update it.');
        }

        $headerSetting = new HeaderSetting();
        $headerSetting->fill($this->validatedAttributes($request));
        $headerSetting->save();

        return redirect()->route('header-settings.index')->with('success', 'Header setting created successfully.');
    }

    public function edit(HeaderSetting $headerSetting)
    {
        return view('header-settings.edit', compact('headerSetting'));
    }

    public function update(Request $request, HeaderSetting $headerSetting)
    {
        $headerSetting->fill($this->validatedAttributes($request, $headerSetting));
        $headerSetting->save();

        return redirect()->route('header-settings.index')->with('success', 'Header setting updated successfully.');
    }

    protected function validatedAttributes(Request $request, ?HeaderSetting $headerSetting = null): array
    {
        $validated = $request->validate([
            'header_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:4096'],
            'show_language_switcher' => ['nullable', 'boolean'],
            'show_currency_switcher' => ['nullable', 'boolean'],
            'enable_sticky_header' => ['nullable', 'boolean'],
            'top_header_bg_color' => ['required', 'string', 'max:20'],
            'bottom_header_bg_color' => ['required', 'string', 'max:20'],
            'top_header_text_color' => ['required', 'string', 'max:20'],
            'bottom_header_text_color' => ['required', 'string', 'max:20'],
            'help_line_number' => ['nullable', 'string', 'max:50'],
            'header_nav_menu' => ['nullable', 'array'],
            'header_nav_menu.*.label' => ['nullable', 'string', 'max:100'],
            'header_nav_menu.*.url' => ['nullable', 'string', 'max:255'],
        ]);

        if ($request->hasFile('header_logo')) {
            if ($headerSetting?->header_logo) {
                Storage::disk('public')->delete($headerSetting->header_logo);
            }

            $validated['header_logo'] = $request->file('header_logo')->store('settings/header', 'public');
        } elseif ($headerSetting) {
            $validated['header_logo'] = $headerSetting->header_logo;
        }

        $validated['show_language_switcher'] = $request->boolean('show_language_switcher');
        $validated['show_currency_switcher'] = $request->boolean('show_currency_switcher');
        $validated['enable_sticky_header'] = $request->boolean('enable_sticky_header');

        $navMenu = collect($request->input('header_nav_menu', []))
            ->map(function ($item) {
                return [
                    'label' => trim((string) ($item['label'] ?? '')),
                    'url' => trim((string) ($item['url'] ?? '')),
                ];
            })
            ->filter(fn ($item) => $item['label'] !== '' || $item['url'] !== '')
            ->values()
            ->all();

        $validated['header_nav_menu'] = $navMenu;

        return $validated;
    }

    protected function defaultAttributes(): array
    {
        return [
            'show_language_switcher' => true,
            'show_currency_switcher' => true,
            'enable_sticky_header' => true,
            'top_header_bg_color' => '#FFFFFF',
            'bottom_header_bg_color' => '#FFFFFF',
            'top_header_text_color' => '#000000',
            'bottom_header_text_color' => '#000000',
            'help_line_number' => '',
            'header_nav_menu' => [
                ['label' => 'Home', 'url' => '/'],
                ['label' => 'Flash Sale', 'url' => '/flash-deals'],
                ['label' => 'Blogs', 'url' => '/blog'],
                ['label' => 'All Brands', 'url' => '/brands'],
                ['label' => 'All Categories', 'url' => '/categories'],
                ['label' => 'Sellers', 'url' => '/sellers'],
                ['label' => 'Contact us', 'url' => '/contact-us'],
            ],
        ];
    }
}
