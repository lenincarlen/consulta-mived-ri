<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class TestWebLogin extends Command
{
    protected $signature = 'login:test {email} {password}';
    protected $description = 'Test web login process step by step';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("Testing web login for: {$email}");
        
        try {
            // Step 1: Search for user in LDAP (using service account)
            $this->info('1. Searching for user in LDAP...');
            try {
                $ldapUser = LdapUser::where('mail', $email)->first();
                if (!$ldapUser) {
                    $this->error('✗ User not found in LDAP');
                    return 1;
                }
                $this->info('✓ User found in LDAP');
                $this->info("  - CN: " . ($ldapUser->getFirstAttribute('cn') ?? 'N/A'));
                $this->info("  - Username: " . ($ldapUser->getFirstAttribute('samaccountname') ?? 'N/A'));
                $this->info("  - DN: " . $ldapUser->getDn());
            } catch (\Exception $e) {
                $this->error('✗ LDAP search failed: ' . $e->getMessage());
                return 1;
            }

            // Step 2: Test Laravel Auth::attempt (this is what the web login does)
            $this->info('2. Testing Laravel Auth::attempt...');
            try {
                $result = Auth::attempt(['email' => $email, 'password' => $password]);
                
                if ($result) {
                    $this->info('✓ Authentication successful!');
                    $user = Auth::user();
                    $this->info("  - User ID: " . $user->id);
                    $this->info("  - Name: " . $user->name);
                    $this->info("  - Email: " . $user->email);
                    
                    // Logout
                    Auth::logout();
                    $this->info('✓ Logged out successfully');
                } else {
                    $this->error('✗ Authentication failed');
                    
                    // Check if user exists in database
                    $this->info('3. Checking database for user...');
                    $dbUser = \App\Models\User::where('email', $email)->first();
                    if ($dbUser) {
                        $this->info('✓ User found in database');
                        $this->info("  - ID: " . $dbUser->id);
                        $this->info("  - Name: " . $dbUser->name);
                        $this->info("  - Email: " . $dbUser->email);
                        $this->info("  - Created: " . $dbUser->created_at);
                    } else {
                        $this->info('✗ User not found in database');
                    }
                }
            } catch (\Exception $e) {
                $this->error('✗ Auth::attempt failed: ' . $e->getMessage());
                $this->error('Exception class: ' . get_class($e));
                Log::error('Auth::attempt Exception', [
                    'user' => $email,
                    'error' => $e->getMessage(),
                    'class' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);
                return 1;
            }

        } catch (\Exception $e) {
            $this->error("✗ General Exception: {$e->getMessage()}");
            $this->error('Exception class: ' . get_class($e));
            Log::error('General Exception in web login', [
                'user' => $email,
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        $this->info('Web login test completed successfully!');
        return 0;
    }
}
