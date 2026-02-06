<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Display all settings grouped by category.
     * Shows settings organized by: general, hotspot, billing, and system.
     */
    public function index(Request $request)
    {
        // Get all settings grouped by category
        $generalSettings = Setting::byGroup('general')->orderBy('key')->get();
        $hotspotSettings = Setting::byGroup('hotspot')->orderBy('key')->get();
        $billingSettings = Setting::byGroup('billing')->orderBy('key')->get();
        $systemSettings = Setting::byGroup('system')->orderBy('key')->get();

        // Get formatted values
        $settings = [
            'general' => $generalSettings->mapWithKeys(function ($setting) {
                return [$setting->key => [
                    'value' => $setting->getValue(),
                    'type' => $setting->type,
                    'description' => $setting->description,
                ]];
            }),
            'hotspot' => $hotspotSettings->mapWithKeys(function ($setting) {
                return [$setting->key => [
                    'value' => $setting->getValue(),
                    'type' => $setting->type,
                    'description' => $setting->description,
                ]];
            }),
            'billing' => $billingSettings->mapWithKeys(function ($setting) {
                return [$setting->key => [
                    'value' => $setting->getValue(),
                    'type' => $setting->type,
                    'description' => $setting->description,
                ]];
            }),
            'system' => $systemSettings->mapWithKeys(function ($setting) {
                return [$setting->key => [
                    'value' => $setting->getValue(),
                    'type' => $setting->type,
                    'description' => $setting->description,
                ]];
            }),
        ];

        // Initialize default settings if they don't exist
        $this->initializeDefaultSettings();

        return view('settings.index', compact('settings'));
    }

    /**
     * Update multiple settings at once (mass update).
     * Allows updating settings from different groups in a single request.
     */
    public function update(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'nullable',
        ]);

        DB::beginTransaction();

        try {
            foreach ($validated['settings'] as $key => $value) {
                // Skip if value is null or empty string (unless it's intentional)
                if ($value === null) {
                    continue;
                }

                // Get the setting to determine its type
                $setting = Setting::byKey($key)->first();

                if ($setting) {
                    // Update existing setting
                    $setting->setValue($value);
                } else {
                    // Create new setting (auto-detect type and group)
                    $group = $this->detectSettingGroup($key);
                    Setting::set($key, $value, null, $group);
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Settings updated successfully.',
                ]);
            }

            return redirect()
                ->route('settings.index')
                ->with('success', 'Settings updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update settings: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific setting value (API endpoint).
     * Returns the setting value or default if not found.
     */
    public function get(Request $request, string $key)
    {
        $default = $request->get('default');
        $value = Setting::get($key, $default);

        return response()->json([
            'key' => $key,
            'value' => $value,
            'exists' => Setting::has($key),
        ]);
    }

    /**
     * Set a specific setting value (API endpoint).
     * Creates or updates a single setting.
     */
    public function set(Request $request, string $key)
    {
        $validated = $request->validate([
            'value' => 'required',
            'type' => ['nullable', Rule::in(['string', 'integer', 'boolean', 'json'])],
            'group' => ['nullable', Rule::in(['general', 'hotspot', 'billing', 'system'])],
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $type = $validated['type'] ?? null;
            $group = $validated['group'] ?? $this->detectSettingGroup($key);

            Setting::set($key, $validated['value'], $type, $group);

            // Update description if provided
            if (isset($validated['description'])) {
                $setting = Setting::byKey($key)->first();
                if ($setting) {
                    $setting->update(['description' => $validated['description']]);
                }
            }

            $setting = Setting::byKey($key)->first();

            return response()->json([
                'success' => true,
                'message' => 'Setting saved successfully.',
                'setting' => [
                    'key' => $setting->key,
                    'value' => $setting->getValue(),
                    'type' => $setting->type,
                    'group' => $setting->group,
                    'description' => $setting->description,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save setting: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Initialize default settings if they don't exist.
     * Creates common application settings with default values.
     */
    private function initializeDefaultSettings(): void
    {
        $defaults = [
            // General Settings
            'app_name' => [
                'value' => 'ElgioTik',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Application name displayed throughout the system',
            ],
            'currency' => [
                'value' => 'USD',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default currency code (e.g., USD, EUR, IDR)',
            ],
            'currency_symbol' => [
                'value' => '$',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Currency symbol for display',
            ],
            'timezone' => [
                'value' => 'UTC',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default timezone for the application',
            ],
            'date_format' => [
                'value' => 'Y-m-d',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Date format (PHP date format)',
            ],
            'time_format' => [
                'value' => 'H:i:s',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Time format (PHP date format)',
            ],
            'datetime_format' => [
                'value' => 'Y-m-d H:i:s',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Date and time format (PHP date format)',
            ],

            // Hotspot Settings
            'voucher_code_prefix' => [
                'value' => '',
                'type' => 'string',
                'group' => 'hotspot',
                'description' => 'Prefix for generated voucher codes',
            ],
            'voucher_code_length' => [
                'value' => 12,
                'type' => 'integer',
                'group' => 'hotspot',
                'description' => 'Length of generated voucher codes (including prefix)',
            ],
            'default_session_timeout' => [
                'value' => 3600,
                'type' => 'integer',
                'group' => 'hotspot',
                'description' => 'Default session timeout in seconds (1 hour)',
            ],
            'default_idle_timeout' => [
                'value' => 600,
                'type' => 'integer',
                'group' => 'hotspot',
                'description' => 'Default idle timeout in seconds (10 minutes)',
            ],
            'max_concurrent_sessions' => [
                'value' => 1,
                'type' => 'integer',
                'group' => 'hotspot',
                'description' => 'Maximum concurrent sessions per voucher',
            ],
            'auto_disconnect_on_expire' => [
                'value' => true,
                'type' => 'boolean',
                'group' => 'hotspot',
                'description' => 'Automatically disconnect users when voucher expires',
            ],

            // Billing Settings
            'enable_billing' => [
                'value' => true,
                'type' => 'boolean',
                'group' => 'billing',
                'description' => 'Enable billing and payment features',
            ],
            'tax_rate' => [
                'value' => 0,
                'type' => 'integer',
                'group' => 'billing',
                'description' => 'Tax rate percentage (0-100)',
            ],
            'invoice_prefix' => [
                'value' => 'INV-',
                'type' => 'string',
                'group' => 'billing',
                'description' => 'Invoice number prefix',
            ],
            'invoice_number_length' => [
                'value' => 6,
                'type' => 'integer',
                'group' => 'billing',
                'description' => 'Invoice number length (excluding prefix)',
            ],
            'payment_methods' => [
                'value' => ['cash', 'bank_transfer', 'credit_card'],
                'type' => 'json',
                'group' => 'billing',
                'description' => 'Available payment methods',
            ],

            // System Settings
            'enable_email_notifications' => [
                'value' => false,
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Enable email notifications',
            ],
            'enable_sms_notifications' => [
                'value' => false,
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Enable SMS notifications',
            ],
            'notification_email' => [
                'value' => '',
                'type' => 'string',
                'group' => 'system',
                'description' => 'Email address for system notifications',
            ],
            'sms_provider' => [
                'value' => 'twilio',
                'type' => 'string',
                'group' => 'system',
                'description' => 'SMS provider (twilio, nexmo, etc.)',
            ],
            'maintenance_mode' => [
                'value' => false,
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Enable maintenance mode',
            ],
            'session_lifetime' => [
                'value' => 120,
                'type' => 'integer',
                'group' => 'system',
                'description' => 'User session lifetime in minutes',
            ],
            'pagination_per_page' => [
                'value' => 15,
                'type' => 'integer',
                'group' => 'system',
                'description' => 'Default number of items per page',
            ],
            'enable_api' => [
                'value' => true,
                'type' => 'boolean',
                'group' => 'system',
                'description' => 'Enable API endpoints',
            ],
            'api_rate_limit' => [
                'value' => 60,
                'type' => 'integer',
                'group' => 'system',
                'description' => 'API rate limit (requests per minute)',
            ],
        ];

        foreach ($defaults as $key => $config) {
            if (!Setting::has($key)) {
                Setting::create([
                    'key' => $key,
                    'value' => Setting::formatValue($config['value'], $config['type']),
                    'type' => $config['type'],
                    'group' => $config['group'],
                    'description' => $config['description'],
                ]);
            }
        }
    }

    /**
     * Detect the appropriate group for a setting based on its key.
     * Uses key prefixes and patterns to determine the group.
     */
    private function detectSettingGroup(string $key): string
    {
        // Check for common prefixes
        if (str_starts_with($key, 'voucher_') || str_starts_with($key, 'session_') || str_starts_with($key, 'hotspot_')) {
            return 'hotspot';
        }

        if (str_starts_with($key, 'billing_') || str_starts_with($key, 'payment_') || str_starts_with($key, 'invoice_') || str_starts_with($key, 'tax_')) {
            return 'billing';
        }

        if (str_starts_with($key, 'email_') || str_starts_with($key, 'sms_') || str_starts_with($key, 'notification_') || str_starts_with($key, 'api_') || str_starts_with($key, 'maintenance_')) {
            return 'system';
        }

        // Check for specific keywords
        $keywords = [
            'hotspot' => ['timeout', 'disconnect', 'concurrent'],
            'billing' => ['price', 'cost', 'currency', 'charge'],
            'system' => ['enable', 'disable', 'limit', 'lifetime'],
        ];

        foreach ($keywords as $group => $words) {
            foreach ($words as $word) {
                if (str_contains(strtolower($key), $word)) {
                    return $group;
                }
            }
        }

        // Default to general
        return 'general';
    }
}
