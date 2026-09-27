<?php

namespace App\Services\Portal;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * Class PortalService
 *
 * ============================================================================
 * PLUGGABLE ENROLLMENT BACKEND INTERFACE
 * ============================================================================
 *
 * This class is the SINGLE place you need to customize to connect WhatsEnroll
 * to your external system after a payment is captured and verified.
 *
 * Whether you use:
 *  - An external Learning Management System (LMS) API (Moodle, Teachable, custom portal)
 *  - A CRM or Webhook (HubSpot, Zapier, Make.com, Google Sheets)
 *  - An internal database-only flow (no external API calls)
 *  - An email or Slack notification to your operations team
 *
 * CONTRACT REQUIREMENTS:
 * ----------------------------------------------------------------------------
 * Input ($data array):
 *  - 'name'         => (string) Student full name
 *  - 'email'        => (string) Student email address
 *  - 'phone'        => (string) Student phone number with country code (e.g. 919876543210)
 *  - 'course_id'    => (int|string) External course identifier / portal_course_id
 *  - 'amount'       => (float)  Amount paid
 *  - 'utr_number'   => (string) Razorpay payment ID or manual UTR number
 *  - 'payment_type' => (string) 'full' or 'installment'
 *
 * Return ($result array):
 *  On Success:
 *  [
 *      'success' => true,
 *      'data'    => [
 *          'username'     => 'student_username_or_email',
 *          'password'     => 'generated_temp_password', // required for new user delivery
 *          'portal_url'   => 'https://your-lms.com/login',
 *          'course_title' => 'Laravel Mastery',
 *          'is_new_user'  => true, // boolean: true sends credentials, false sends confirmation
 *      ]
 *  ]
 *
 *  On Failure:
 *  [
 *      'success' => false,
 *      'message' => 'Descriptive error message',
 *  ]
 * ============================================================================
 */
class PortalService
{
    protected ?Client $client;
    protected ?string $apiUrl;
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.portal.api_url');
        $this->apiKey = config('services.portal.api_key');
        $this->client = $this->apiUrl ? new Client(['timeout' => 30]) : null;
    }

    /**
     * Enroll student into the backend LMS / CRM.
     *
     * @param array $data Student and enrollment details
     * @return array Standardized result payload
     */
    public function enrollStudent(array $data): array
    {
        // If no external portal is configured, fall back to an internal success payload
        if (empty($this->apiUrl)) {
            Log::info('PortalService: No external PORTAL_API_URL configured. Using internal default response.');
            return [
                'success' => true,
                'data'    => [
                    'username'     => $data['email'],
                    'password'     => 'Temp' . rand(100000, 999999) . '!',
                    'portal_url'   => config('app.url', 'http://localhost'),
                    'course_title' => $data['course_title'] ?? 'Enrolled Course',
                    'is_new_user'  => true,
                ],
            ];
        }

        try {
            $response = $this->client->post($this->apiUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-Api-Key'    => $this->apiKey,
                ],
                'json' => $data,
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            Log::info('Portal enrollment response:', $result ?? []);
            return $result ?? ['success' => false, 'message' => 'Empty response from portal'];

        } catch (\Exception $e) {
            Log::error('Portal API error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
