<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function __construct(
        private GoogleSheetsService $sheets
    ) {}

    /**
     * Show settings form (communication: phone_number, email, whatsapps). Load from sheet tab "comunication".
     */
    public function index()
    {
        $loadError = null;
        try {
            $settings = $this->sheets->getCommunicationSettings();
        } catch (\Throwable $e) {
            Log::warning('Settings: could not load from Google Sheet', ['message' => $e->getMessage()]);
            $settings = ['phone_number' => '', 'email' => '', 'whatsapps' => ''];
            $loadError = $e->getMessage();
        }

        return view('settings.index', compact('settings', 'loadError'));
    }

    /**
     * Save communication settings to Google Sheet tab "communication".
     */
    public function update(Request $request)
    {
        $request->validate([
            'phone_number' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'whatsapps' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $this->sheets->setCommunicationSettings([
                'phone_number' => $request->input('phone_number', ''),
                'email' => $request->input('email', ''),
                'whatsapps' => $request->input('whatsapps', ''),
            ]);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', __('settings_save_error'));
        }

        return redirect()->route('settings.index')->with('success', __('settings_saved'));
    }
}
