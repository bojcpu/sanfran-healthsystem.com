<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public function log(string $action, ?string $description = null, ?string $module = null, ?User $user = null): ActivityLog
    {
        return ActivityLog::query()->create([
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action,
            'description' => $description,
            'module' => $module,
            'ip_address' => Request::ip(),
            'device_name' => $this->deviceName(Request::userAgent()),
        ]);
    }

    private function deviceName(?string $userAgent): string
    {
        $userAgent = strtolower($userAgent ?? '');

        $device = match (true) {
            str_contains($userAgent, 'ipad') => 'iPad',
            str_contains($userAgent, 'iphone') => 'iPhone',
            str_contains($userAgent, 'android') && str_contains($userAgent, 'mobile') => 'Android phone',
            str_contains($userAgent, 'android') => 'Android tablet',
            str_contains($userAgent, 'windows') => 'Windows PC',
            str_contains($userAgent, 'macintosh'), str_contains($userAgent, 'mac os') => 'Mac',
            str_contains($userAgent, 'linux') => 'Linux device',
            default => 'Unknown device',
        };

        $browser = match (true) {
            str_contains($userAgent, 'edg/') => 'Edge',
            str_contains($userAgent, 'opr/') => 'Opera',
            str_contains($userAgent, 'samsungbrowser/') => 'Samsung Internet',
            str_contains($userAgent, 'firefox/'), str_contains($userAgent, 'fxios/') => 'Firefox',
            str_contains($userAgent, 'chrome/'), str_contains($userAgent, 'crios/') => 'Chrome',
            str_contains($userAgent, 'safari/') => 'Safari',
            str_contains($userAgent, 'msie '), str_contains($userAgent, 'trident/') => 'Internet Explorer',
            default => 'Unknown browser',
        };

        return "{$device} — {$browser}";
    }
}
