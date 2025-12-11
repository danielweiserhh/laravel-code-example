<?php

use App\Services\DefaultDataService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
        $service = app(DefaultDataService::class);

        
        DB::table('users')->orderBy('id')->chunk(100, function ($users) use ($service) {
            foreach ($users as $userData) {
                $user = \App\Models\User::find($userData->id);
                if ($user && ! $user->workspaces()->exists()) {
                    $service->createDefaultWorkspaceForUser($user);
                }
            }
        });
    }

    
    public function down(): void
    {
        
        
    }
};
