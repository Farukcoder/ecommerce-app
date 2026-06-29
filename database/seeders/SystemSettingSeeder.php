<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $attributes = [
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
            'about_mission_image' => null,
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
            'flash_deal_page_banner_large' => null,
            'flash_deal_page_banner_small' => null,
            'product_default_image' => null,
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
            'free_shipping_for_everyone' => false,
            'default_shipping_rate' => 80.00,
        ];

        $systemSetting = SystemSetting::query()->latest('id')->first();

        if ($systemSetting) {
            $systemSetting->update($attributes);

            return;
        }

        SystemSetting::create($attributes);
    }
}
