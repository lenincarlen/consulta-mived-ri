<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Connection;
use App\Ldap\Rules\OnlyHelpDeskUsers;

class TestLdapAuth extends Command
{
    protected $signature = 'ldap:auth {email} {password}';
    protected $description = 'Test LDAP authentication step by step';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $this->info("Testing LDAP authentication for: {$email}");
        
        try {
            // Step 1: Find user in LDAP
            $this->info('1. Finding user in LDAP...');
            $ldapUser = LdapUser::where('mail', $email)->first();
            if (!$ldapUser) {
                $this->error('✗ User not found in LDAP');
                return 1;
            }
            $this->info('✓ User found in LDAP');
            $this->info("  - CN: " . ($ldapUser->getFirstAttribute('cn') ?? 'N/A'));
            $this->info("  - Username: " . ($ldapUser->getFirstAttribute('samaccountname') ?? 'N/A'));
            $this->info("  - DN: " . $ldapUser->getDn());

            // Step 2: Check if user passes the rule
            $this->info('2. Checking if user passes validation rule...');
            $rule = new OnlyHelpDeskUsers();
            $isValid = $rule->passes($ldapUser);
            if ($isValid) {
                $this->info('✓ User passes validation rule');
            } else {
                $this->error('✗ User failed validation rule');
                return 1;
            }

            // Step 3: Test LDAP bind with user credentials
            $this->info('3. Testing LDAP bind with user credentials...');
            try {
                $connection = new Connection(config('ldap.connections.default'));
                $connection->connect();
                
                // Try to bind with user credentials
                $connection->auth()->bind($ldapUser->getDn(), $password);
                $this->info('✓ LDAP bind successful with user credentials');
            } catch (\Exception $e) {
                $this->error('✗ LDAP bind failed: ' . $e->getMessage());
                $this->error('This means the user password is incorrect or the user cannot bind directly');
                return 1;
            }

            // Step 4: Check if user exists in local database
            $this->info('4. Checking local database...');
            $dbUser = \App\Models\User::where('email', $email)->first();
            if ($dbUser) {
                $this->info('✓ User exists in local database');
                $this->info("  - ID: " . $dbUser->id);
                $this->info("  - Name: " . $dbUser->name);
                $this->info("  - Email: " . $dbUser->email);
            } else {
                $this->info('✗ User not found in local database');
                $this->info('This is expected for first-time login');
            }

            $this->info('✓ LDAP authentication test completed successfully!');
            $this->info('The user should be able to authenticate through Laravel');
            
        } catch (\Exception $e) {
            $this->error("✗ Exception: {$e->getMessage()}");
            $this->error('Exception class: ' . get_class($e));
            Log::error('LDAP Auth Test Exception', [
                'user' => $email,
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        return 0;
    }
}
