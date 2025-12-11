<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $user = User::where('email', $email)->first();
        
        if (! $user) {
            return $this->success(null, 'Если email зарегистрирован, вы получите письмо с инструкциями.');
        }
        
        if ($user->google_id && ! $user->password) {
            return $this->success(null, 'Если email зарегистрирован, вы получите письмо с инструкциями.');
        }
        
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        $token = Str::random(64);
        
        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);
        
        $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
        $resetUrl = "{$frontendUrl}/reset-password?token={$token}&email=".urlencode($email);
        
        Mail::to($user)->send(new PasswordResetMail($user, $resetUrl));

        return $this->success(null, 'Если email зарегистрирован, вы получите письмо с инструкциями.');
    }
    
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = $request->input('email');
        $token = $request->input('token');
        $password = $request->input('password');

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $tokenRecord) {
            return $this->error('Недействительная ссылка для сброса пароля.', 422);
        }
        
        $tokenCreatedAt = strtotime($tokenRecord->created_at);

        if (time() - $tokenCreatedAt > 3600) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();

            return $this->error('Ссылка для сброса пароля истекла. Запросите новую.', 422);
        }
        
        if (! Hash::check($token, $tokenRecord->token)) {
            return $this->error('Недействительная ссылка для сброса пароля.', 422);
        }
        
        $user = User::where('email', $email)->first();

        if (! $user) {
            return $this->error('Пользователь не найден.', 404);
        }
        
        $user->password = $password; 
        $user->save();
        
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        
        $user->tokens()->delete();

        return $this->success(null, 'Пароль успешно изменён. Теперь вы можете войти с новым паролем.');
    }
}
