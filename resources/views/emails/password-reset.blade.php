<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Сброс пароля</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: #ffffff;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        h1 {
            color: #1a1a2e;
            font-size: 22px;
            margin: 0 0 20px;
        }
        p {
            color: #4a5568;
            margin: 0 0 16px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 600;
            margin: 20px 0;
        }
        .button:hover {
            opacity: 0.9;
        }
        .link-text {
            word-break: break-all;
            font-size: 12px;
            color: #718096;
            background: #f7fafc;
            padding: 12px;
            border-radius: 6px;
            margin: 16px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 13px;
            color: #a0aec0;
            text-align: center;
        }
        .warning {
            background: #fffbeb;
            border: 1px solid #fbbf24;
            border-radius: 6px;
            padding: 12px 16px;
            font-size: 13px;
            color: #92400e;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">Kanban Board</div>
        </div>

        <h1>Сброс пароля</h1>
        
        <p>Здравствуйте, {{ $user->name }}!</p>
        
        <p>Мы получили запрос на сброс пароля для вашего аккаунта. Нажмите кнопку ниже, чтобы создать новый пароль:</p>
        
        <div style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">Сбросить пароль</a>
        </div>
        
        <p>Или скопируйте эту ссылку в браузер:</p>
        <div class="link-text">{{ $resetUrl }}</div>
        
        <div class="warning">
            ⚠️ Ссылка действительна в течение 1 часа. Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.
        </div>
        
        <div class="footer">
            <p>Это автоматическое сообщение от Kanban Board.</p>
            <p>Если у вас возникли вопросы, свяжитесь с поддержкой.</p>
        </div>
    </div>
</body>
</html>

