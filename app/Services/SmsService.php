<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiKey;
    protected $senderName;
    protected $apiUrl = 'https://api.semaphore.co/api/v4/messages';

    public function __construct()
    {
        $this->apiKey = config('services.semaphore.key');
        $this->senderName = config('services.semaphore.sender');
    }

    /**
     * Send SMS to a single phone number
     *
     * @param string $phoneNumber
     * @param string $message
     * @return array
     */
    public function sendSms(string $phoneNumber, string $message): array
    {
        return $this->sendBulkSms([$phoneNumber], $message);
    }

    /**
     * Send SMS to multiple phone numbers
     *
     * @param array $phoneNumbers
     * @param string $message
     * @param mixed $notifiable The model this SMS is related to (Event, Assignment, etc.)
     * @param array $recipientData Array of recipient data with keys: phone_number, user_id, recipient_type
     * @return array
     */
    public function sendBulkSms(array $phoneNumbers, string $message, $notifiable = null, array $recipientData = []): array
    {
        // Validate API key
        if (empty($this->apiKey)) {
            Log::warning('SMS API key not configured. Skipping SMS sending.');
            return [
                'success' => false,
                'message' => 'SMS API key not configured',
                'sent_count' => 0
            ];
        }

        // Filter and format phone numbers
        $validPhoneNumbers = [];
        foreach ($phoneNumbers as $phoneNumber) {
            $formatted = $this->formatPhoneNumber($phoneNumber);
            if ($formatted) {
                $validPhoneNumbers[] = $formatted;
            }
        }

        if (empty($validPhoneNumbers)) {
            Log::warning('No valid phone numbers to send SMS to.');
            return [
                'success' => false,
                'message' => 'No valid phone numbers',
                'sent_count' => 0
            ];
        }

        // Remove duplicates
        $validPhoneNumbers = array_unique($validPhoneNumbers);

        // Create pending SMS notification records if notifiable is provided
        $smsNotifications = [];
        if ($notifiable) {
            foreach ($recipientData as $recipient) {
                if (in_array($this->formatPhoneNumber($recipient['phone_number']), $validPhoneNumbers)) {
                    $smsNotifications[] = \App\Models\SmsNotification::create([
                        'notifiable_type' => get_class($notifiable),
                        'notifiable_id' => $notifiable->id,
                        'phone_number' => $this->formatPhoneNumber($recipient['phone_number']),
                        'recipient_type' => $recipient['recipient_type'] ?? null,
                        'user_id' => $recipient['user_id'] ?? null,
                        'message' => $message,
                        'sender_name' => $this->senderName,
                        'status' => \App\Models\SmsNotification::STATUS_PENDING,
                    ]);
                }
            }
        }

        try {
            // Convert phone numbers to international format for Semaphore API
            // Semaphore requires format: 639XXXXXXXXX (not 09XXXXXXXXX)
            $internationalNumbers = array_map(function($number) {
                // If number starts with 0, replace with 63
                if (substr($number, 0, 1) === '0') {
                    return '63' . substr($number, 1);
                }
                return $number;
            }, $validPhoneNumbers);

            // Semaphore API expects comma-separated phone numbers
            $recipients = implode(',', $internationalNumbers);

            $response = Http::asForm()->post($this->apiUrl, [
                'apikey' => $this->apiKey,
                'number' => $recipients,
                'message' => $message,
                'sendername' => $this->senderName,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('SMS sent successfully', [
                    'recipients_count' => count($validPhoneNumbers),
                    'response' => $responseData
                ]);

                // Update SMS notification records to sent status
                foreach ($smsNotifications as $smsNotification) {
                    $smsNotification->markAsSent(
                        $responseData,
                        $responseData['message_id'] ?? null
                    );
                }

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'sent_count' => count($validPhoneNumbers),
                    'response' => $responseData
                ];
            } else {
                Log::error('Failed to send SMS', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);

                // Update SMS notification records to failed status
                foreach ($smsNotifications as $smsNotification) {
                    $smsNotification->markAsFailed(
                        'API Error: ' . $response->body(),
                        ['status' => $response->status(), 'body' => $response->body()]
                    );
                }

                return [
                    'success' => false,
                    'message' => 'Failed to send SMS: ' . $response->body(),
                    'sent_count' => 0
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending SMS', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update SMS notification records to failed status
            foreach ($smsNotifications as $smsNotification) {
                $smsNotification->markAsFailed(
                    'Exception: ' . $e->getMessage(),
                    ['exception' => $e->getMessage()]
                );
            }

            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'sent_count' => 0
            ];
        }
    }

    /**
     * Format phone number for Philippine mobile numbers
     * Converts various formats to 09XXXXXXXXX format (11 digits)
     *
     * @param string|null $phoneNumber
     * @return string|null
     */
    public function formatPhoneNumber(?string $phoneNumber): ?string
    {
        if (empty($phoneNumber)) {
            return null;
        }

        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (empty($cleaned)) {
            return null;
        }

        // Handle different formats
        // Format: 09XXXXXXXXX (11 digits starting with 0) - already in correct format
        if (strlen($cleaned) === 11 && substr($cleaned, 0, 1) === '0') {
            return $cleaned;
        }

        // Format: 9XXXXXXXXX (10 digits) - add 0 prefix
        if (strlen($cleaned) === 10 && substr($cleaned, 0, 1) === '9') {
            return '0' . $cleaned;
        }

        // Format: 639XXXXXXXXX (12 digits starting with 63) - convert to 09XX
        if (strlen($cleaned) === 12 && substr($cleaned, 0, 2) === '63') {
            return '0' . substr($cleaned, 2);
        }

        // Format: +639XXXXXXXXX (remove +63 and add 0)
        if (strlen($phoneNumber) === 13 && substr($phoneNumber, 0, 3) === '+63') {
            return '0' . substr($cleaned, 2);
        }

        // Invalid format
        Log::warning('Invalid phone number format', ['phone' => $phoneNumber]);
        return null;
    }

    /**
     * Validate if phone number is valid Philippine mobile number
     *
     * @param string|null $phoneNumber
     * @return bool
     */
    public function isValidPhoneNumber(?string $phoneNumber): bool
    {
        return $this->formatPhoneNumber($phoneNumber) !== null;
    }
}
