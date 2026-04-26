<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Database\Seeders\SettingSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(): Response
    {
        $settings = Setting::all()->groupBy('group');

        $grouped = [];
        foreach ($settings as $group => $items) {
            $grouped[$group] = $items->map(fn (Setting $s) => [
                'key' => $s->key,
                'value' => Setting::getValue($s->key),
                'type' => $s->type,
                'label' => $s->label,
                'description' => $s->description,
            ])->values()->toArray();
        }

        return Inertia::render('Settings/Index', [
            'settings' => $grouped,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->input('settings', []);

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        Setting::clearCache();

        return redirect()->route('settings.index')
            ->with('success', 'Settings berhasil disimpan!');
    }

    public function reset(): RedirectResponse
    {
        $seeder = new SettingSeeder;
        $seeder->run();

        Setting::clearCache();

        return redirect()->route('settings.index')
            ->with('success', 'Settings berhasil direset ke default!');
    }
}
