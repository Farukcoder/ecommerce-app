<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('about_hero_heading')->nullable()->after('hero_description');
            $table->text('about_hero_description')->nullable()->after('about_hero_heading');
            $table->string('about_mission_heading')->nullable()->after('about_hero_description');
            $table->text('about_mission_description')->nullable()->after('about_mission_heading');
            $table->string('about_mission_image')->nullable()->after('about_mission_description');
            $table->string('about_mission_button_text')->nullable()->after('about_mission_image');
            $table->string('about_mission_button_url')->nullable()->after('about_mission_button_text');
            $table->string('about_values_heading')->nullable()->after('about_mission_button_url');
            $table->string('about_values_subheading')->nullable()->after('about_values_heading');
            $table->json('about_values')->nullable()->after('about_values_subheading');
            $table->string('about_team_heading')->nullable()->after('about_values');
            $table->string('about_team_subheading')->nullable()->after('about_team_heading');
            $table->json('about_team_members')->nullable()->after('about_team_subheading');
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};
