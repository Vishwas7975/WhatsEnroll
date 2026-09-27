<?php

namespace App\Services\Bot;

class LanguageService
{
    protected array $messages = [
        'en' => [
            'welcome' => "Welcome to WhatsEnroll! 🎓",
            'select_language' => "Please select your language:",
            'main_menu' => "How can we help you today?",
            'courses' => "Here are our available courses:",
            'internships' => "Here are our available internships:",
            'support' => "For support contact: support@example.com",
            'payment_pending' => "Please complete your payment to enroll.",
            'enrollment_success' => "🎉 Enrollment successful! Your credentials will be sent shortly.",
            'invalid_option' => "Please select a valid option.",
        ],
        'hi' => [
            'welcome' => "WhatsEnroll में आपका स्वागत है! 🎓",
            'select_language' => "कृपया अपनी भाषा चुनें:",
            'main_menu' => "हम आपकी कैसे मदद कर सकते हैं?",
            'courses' => "हमारे उपलब्ध कोर्स:",
            'internships' => "हमारी उपलब्ध इंटर्नशिप:",
            'support' => "सहायता के लिए: support@example.com",
            'payment_pending' => "कृपया नामांकन के लिए भुगतान पूरा करें।",
            'enrollment_success' => "🎉 नामांकन सफल! आपकी जानकारी जल्द भेजी जाएगी।",
            'invalid_option' => "कृपया एक वैध विकल्प चुनें।",
        ],
        'te' => [
            'welcome' => "WhatsEnroll కు స్వాగతం! 🎓",
            'select_language' => "దయచేసి మీ భాష ఎంచుకోండి:",
            'main_menu' => "మేము మీకు ఎలా సహాయం చేయగలం?",
            'courses' => "మా అందుబాటులో ఉన్న కోర్సులు:",
            'internships' => "మా అందుబాటులో ఉన్న ఇంటర్న్‌షిప్‌లు:",
            'support' => "సహాయానికి: support@example.com",
            'payment_pending' => "దయచేసి చేరిక కోసం చెల్లింపు పూర్తి చేయండి।",
            'enrollment_success' => "🎉 చేరిక విజయవంతమైంది! మీ వివరాలు త్వరలో పంపబడతాయి.",
            'invalid_option' => "దయచేసి చెల్లుబాటు అయ్యే ఎంపిక ఎంచుకోండి.",
        ],
    ];

    public function get(string $key, string $lang = 'en'): string
    {
        return $this->messages[$lang][$key]
            ?? $this->messages['en'][$key]
            ?? $key;
    }
}