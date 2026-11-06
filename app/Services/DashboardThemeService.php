<?php

namespace App\Services;

use App\Models\ThemeSetting;

class DashboardThemeService
{
    /** @return array<string, string> */
    public function colors(): array
    {
        $settings = ThemeSetting::query()->where('category', 'inicio')->where('is_active', true)->get()->keyBy('class');
        $colors = [];
        foreach (config('dashboard_theme') as $default) {
            $setting = $settings->get($default['class']);
            $value = $this->validColor($setting?->value, $default['value']);
            if ($default['gradient']) {
                $value2 = $this->validColor($setting?->value2, $default['value2']);
                $value = 'linear-gradient(135deg, '.$value.' 0%, '.$value2.' 100%)';
            }
            $colors[$default['class']] = $value;
        }

        return $colors;
    }

    private function validColor(?string $value, string $default): string
    {
        return is_string($value) && preg_match('/^#[a-f0-9]{6}(?:[a-f0-9]{2})?$/i', $value) ? $value : $default;
    }
}
