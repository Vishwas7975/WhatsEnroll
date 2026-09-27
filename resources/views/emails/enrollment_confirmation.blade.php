<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Confirmed — WhatsEnroll</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #1B4F72, #2E86C1); padding: 30px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 24px; }
        .header p { color: #D6EAF8; margin: 5px 0 0; font-size: 14px; }
        .body { padding: 30px; }
        .greeting { font-size: 18px; color: #1A1A2E; margin-bottom: 20px; }
        .success-badge { background: #D5F5E3; color: #1E8449; padding: 10px 20px; border-radius: 20px; display: inline-block; font-weight: bold; margin-bottom: 20px; }
        .course-card { background: #EBF5FB; border-left: 4px solid #2E86C1; padding: 15px 20px; border-radius: 5px; margin-bottom: 20px; }
        .course-card h3 { margin: 0 0 5px; color: #1B4F72; font-size: 16px; }
        .course-card p { margin: 0; color: #555; font-size: 14px; }
        .credentials-box { background: #1A1A2E; color: #ffffff; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .credentials-box h3 { color: #2E86C1; margin: 0 0 15px; font-size: 16px; }
        .credential-row { display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #333; }
        .credential-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .credential-label { color: #aaa; font-size: 13px; }
        .credential-value { color: #ffffff; font-size: 13px; font-weight: bold; }
        .login-btn { display: block; background: #2E86C1; color: #ffffff; text-align: center; padding: 15px; border-radius: 8px; text-decoration: none; font-size: 16px; font-weight: bold; margin-bottom: 20px; }
        .warning { background: #FEF9E7; border: 1px solid #F9E79F; padding: 12px 15px; border-radius: 5px; font-size: 13px; color: #7D6608; margin-bottom: 20px; }
        .footer { background: #f4f4f4; padding: 20px; text-align: center; font-size: 12px; color: #888; }
        .amount-badge { background: #D5F5E3; color: #1E8449; padding: 5px 12px; border-radius: 15px; font-weight: bold; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>🎓 WhatsEnroll</h1>
            <p>Your Learning Journey Starts Now!</p>
        </div>

        <!-- Body -->
        <div class="body">
            <p class="greeting">Dear <strong>{{ $studentName }}</strong>,</p>

            <div class="success-badge">✅ Enrollment Confirmed!</div>

            <p style="color: #555; font-size: 15px; margin-bottom: 20px;">
                Congratulations! Your enrollment has been successfully processed.
                You now have full access to your course on the learning portal.
            </p>

            <!-- Course Details -->
            <div class="course-card">
                <h3>📚 {{ $courseName }}</h3>
                <p>Amount Paid: <span class="amount-badge">₹{{ number_format($amountPaid, 2) }}</span></p>
            </div>

            @if($isNewUser)
            <!-- Login Credentials -->
            <div class="credentials-box">
                <h3>🔐 Your Login Credentials</h3>
                <div class="credential-row">
                    <span class="credential-label">Portal URL</span>
                    <span class="credential-value">{{ $portalUrl }}</span>
                </div>
                <div class="credential-row">
                    <span class="credential-label">Username (Email)</span>
                    <span class="credential-value">{{ $username }}</span>
                </div>
                <div class="credential-row">
                    <span class="credential-label">Password</span>
                    <span class="credential-value">{{ $password }}</span>
                </div>
            </div>

            <div class="warning">
                ⚠️ <strong>Important:</strong> Please change your password after your first login for security.
            </div>
            @else
            <div class="warning">
                ℹ️ You already have an account. Login with your existing credentials at the portal URL above.
            </div>
            @endif

            <!-- Login Button -->
            <a href="{{ $portalUrl }}" class="login-btn">
                🚀 Login to Portal Now
            </a>

            <p style="color: #555; font-size: 14px;">
                If you have any questions or need support, please reply to this email
                or contact the support team through WhatsApp.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>WhatsEnroll &mdash; All rights reserved.</p>
            <p>This email was sent to {{ $username }}</p>
        </div>
    </div>
</body>
</html>