<?php

namespace App\Services;

use App\Enums\SmsNotificationType;
use App\Enums\SmsStatus;
use App\Models\SmsNotification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(
        string $mobileNumber,
        string $message,
        SmsNotificationType $type,
        User $sentBy,
        ?int $patientId = null,
    ): SmsNotification {
        $notification = SmsNotification::query()->create([
            'patient_id' => $patientId,
            'mobile_number' => $this->formatMobileNumber($mobileNumber),
            'message' => $message,
            'notification_type' => $type,
            'status' => SmsStatus::Pending,
            'sent_by' => $sentBy->id,
        ]);

        try {
            $response = $this->dispatchToIprog($notification->mobile_number, $message);

            $notification->update([
                'status' => $response['success'] ? SmsStatus::Sent : SmsStatus::Failed,
                'sent_at' => now(),
                'delivery_response' => $response['body'],
            ]);
        } catch (\Throwable $exception) {
            Log::error('SMS sending failed', [
                'error' => $exception->getMessage(),
                'notification_id' => $notification->id,
            ]);

            $notification->update([
                'status' => SmsStatus::Failed,
                'delivery_response' => $exception->getMessage(),
            ]);
        }

        return $notification->fresh();
    }

    public function sendCheckupReminder(User $sentBy, int $patientId, string $mobileNumber, string $patientName, string $date): SmsNotification
    {
        $message = "Reminder: Dear {$patientName}, your scheduled checkup is on {$date}. - SanFran HealthSys";

        return $this->send($mobileNumber, $message, SmsNotificationType::CheckupReminder, $sentBy, $patientId);
    }

    public function sendImmunizationReminder(User $sentBy, int $patientId, string $mobileNumber, string $patientName, string $date): SmsNotification
    {
        $message = "Reminder: Dear {$patientName}, your child's immunization schedule is on {$date}. - SanFran HealthSys";

        return $this->send($mobileNumber, $message, SmsNotificationType::ImmunizationReminder, $sentBy, $patientId);
    }

    public function sendMaternalCareReminder(User $sentBy, int $patientId, string $mobileNumber, string $patientName, string $date): SmsNotification
    {
        $message = "Reminder: Dear {$patientName}, your maternal care appointment is on {$date}. - SanFran HealthSys";

        return $this->send($mobileNumber, $message, SmsNotificationType::MaternalCareReminder, $sentBy, $patientId);
    }

    /**
     * @return array{success: bool, body: string}
     */
    protected function dispatchToIprog(string $mobileNumber, string $message): array
    {
        $apiToken = config('services.iprog.api_token');

        if (empty($apiToken)) {
            return [
                'success' => app()->environment('local'),
                'body' => 'iProg API token not configured. Message logged locally for demonstration.',
            ];
        }

        $response = Http::acceptJson()
            ->post('https://www.iprogsms.com/api/v1/sms_messages', [
                'api_token' => $apiToken,
                'phone_number' => ltrim($mobileNumber, '+'),
                'message' => $message,
            ]);
        $responseData = $response->json();

        return [
            'success' => $response->successful()
                && is_array($responseData)
                && (int) ($responseData['status'] ?? 0) === 200,
            'body' => $response->body(),
        ];
    }

    protected function formatMobileNumber(string $number): string
    {
        $number = preg_replace('/\D/', '', $number) ?? '';

        if (str_starts_with($number, '0')) {
            $number = '63'.substr($number, 1);
        }

        if (! str_starts_with($number, '63')) {
            $number = '63'.$number;
        }

        return '+'.$number;
    }
}

