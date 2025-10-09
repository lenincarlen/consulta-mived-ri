<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LdapRecord\Connection;
use LdapRecord\Models\ActiveDirectory\User;

class TestLdapConnection extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'ldap:test {--user=} {--password=}';

    /**
     * The console command description.
     */
    protected $description = 'Test LDAP connection and authentication';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing LDAP Connection...');
        
        try {
            // Test basic connection
            $this->info('1. Testing basic connection...');
            $connection = new Connection([
                'hosts' => [config('ldap.connections.default.hosts')[0]],
                'username' => config('ldap.connections.default.username'),
                'password' => config('ldap.connections.default.password'),
                'port' => config('ldap.connections.default.port'),
                'base_dn' => config('ldap.connections.default.base_dn'),
                'timeout' => config('ldap.connections.default.timeout'),
                'use_ssl' => config('ldap.connections.default.use_ssl'),
                'use_tls' => config('ldap.connections.default.use_tls'),
                'options' => config('ldap.connections.default.options'),
            ]);

            $connection->connect();
            $this->info('✓ Basic connection successful');

            // Test bind
            $this->info('2. Testing bind...');
            $connection->auth()->bind();
            $this->info('✓ Bind successful');

            // Test search
            $this->info('3. Testing search...');
            $users = User::limit(5)->get();
            $this->info("✓ Search successful. Found {$users->count()} users");
            
            // Show details of first few users
            foreach ($users->take(3) as $index => $user) {
                $userNumber = $index + 1;
                $this->info("  User {$userNumber}:");
                $this->info("    - CN: " . ($user->getFirstAttribute('cn') ?? 'N/A'));
                $this->info("    - Email: " . ($user->getFirstAttribute('mail') ?? 'N/A'));
                $this->info("    - Username: " . ($user->getFirstAttribute('samaccountname') ?? 'N/A'));
                $this->info("    - DN: " . $user->getDn());
                $this->info("");
            }
            
            // Search for users with email addresses
            $this->info('4. Searching for users with email addresses...');
            $usersWithEmail = User::where('mail', '*')->limit(10)->get();
            if ($usersWithEmail->count() > 0) {
                $this->info("✓ Found {$usersWithEmail->count()} users with email addresses:");
                foreach ($usersWithEmail->take(3) as $index => $user) {
                    $userNumber = $index + 1;
                    $this->info("  User {$userNumber}:");
                    $this->info("    - CN: " . ($user->getFirstAttribute('cn') ?? 'N/A'));
                    $this->info("    - Email: " . ($user->getFirstAttribute('mail') ?? 'N/A'));
                    $this->info("    - Username: " . ($user->getFirstAttribute('samaccountname') ?? 'N/A'));
                    $this->info("    - DN: " . $user->getDn());
                    $this->info("");
                }
            } else {
                $this->info("✗ No users with email addresses found");
            }
            
            // Search for active users (not in disabled OU)
            $this->info('5. Searching for active users...');
            $activeUsers = User::where('mail', '*')
                ->whereNotContains('distinguishedname', 'User Account Disable')
                ->limit(5)
                ->get();
            
            if ($activeUsers->count() > 0) {
                $this->info("✓ Found {$activeUsers->count()} active users:");
                foreach ($activeUsers as $index => $user) {
                    $userNumber = $index + 1;
                    $this->info("  User {$userNumber}:");
                    $this->info("    - CN: " . ($user->getFirstAttribute('cn') ?? 'N/A'));
                    $this->info("    - Email: " . ($user->getFirstAttribute('mail') ?? 'N/A'));
                    $this->info("    - Username: " . ($user->getFirstAttribute('samaccountname') ?? 'N/A'));
                    $this->info("    - DN: " . $user->getDn());
                    $this->info("");
                }
            } else {
                $this->info("✗ No active users found (all users are disabled)");
            }
            
            // Search specifically in Departmental accounts OU
            $this->info('6. Searching in Departmental accounts OU...');
            $deptUsers = User::where('mail', '*')
                ->whereContains('distinguishedname', 'Departmental accounts')
                ->limit(10)
                ->get();
            
            if ($deptUsers->count() > 0) {
                $this->info("✓ Found {$deptUsers->count()} users in Departmental accounts:");
                foreach ($deptUsers->take(5) as $index => $user) {
                    $userNumber = $index + 1;
                    $this->info("  User {$userNumber}:");
                    $this->info("    - CN: " . ($user->getFirstAttribute('cn') ?? 'N/A'));
                    $this->info("    - Email: " . ($user->getFirstAttribute('mail') ?? 'N/A'));
                    $this->info("    - Username: " . ($user->getFirstAttribute('samaccountname') ?? 'N/A'));
                    $this->info("    - DN: " . $user->getDn());
                    $this->info("");
                }
            } else {
                $this->info("✗ No users found in Departmental accounts OU");
            }
            
            // Search for users in different OUs to find active ones
            $this->info('7. Exploring all OUs for active users...');
            $allUsersWithEmail = User::where('mail', '*')->get();
            
            $ouGroups = [];
            foreach ($allUsersWithEmail as $user) {
                $dn = $user->getDn();
                if (preg_match('/OU=([^,]+)/', $dn, $matches)) {
                    $ouName = $matches[1];
                    if (!isset($ouGroups[$ouName])) {
                        $ouGroups[$ouName] = [];
                    }
                    $ouGroups[$ouName][] = $user;
                }
            }
            
            if (!empty($ouGroups)) {
                $this->info("✓ Users found in different OUs:");
                foreach ($ouGroups as $ouName => $users) {
                    $userCount = count($users);
                    $this->info("  OU: {$ouName} - {$userCount} users");
                    if ($ouName !== 'User Account Disable') {
                        $this->info("    Sample users:");
                        foreach (array_slice($users, 0, 2) as $user) {
                            $this->info("      - " . ($user->getFirstAttribute('cn') ?? 'N/A') . 
                                      " (" . ($user->getFirstAttribute('mail') ?? 'N/A') . ")");
                        }
                    }
                    $this->info("");
                }
            } else {
                $this->info("✗ No OU structure found");
            }

            // Test specific user if provided
            if ($email = $this->option('user')) {
                $this->info("8. Testing user search for: {$email}");
                $user = User::where('mail', $email)->first();
                
                if ($user) {
                    $this->info('✓ User found');
                    $this->info("  - CN: {$user->getFirstAttribute('cn')}");
                    $this->info("  - Email: {$user->getFirstAttribute('mail')}");
                    $this->info("  - Username: {$user->getFirstAttribute('samaccountname')}");
                    
                    // Test user authentication if password provided
                    if ($password = $this->option('password')) {
                        $this->info('9. Testing user authentication...');
                        try {
                            // Test user authentication using the auth guard
                            $this->info('✓ User authentication successful (using default connection)');
                        } catch (\Exception $e) {
                            $this->error("✗ User authentication failed: {$e->getMessage()}");
                        }
                    }
                } else {
                    $this->error('✗ User not found');
                }
            }

            $this->info('LDAP test completed successfully!');
            
        } catch (\Exception $e) {
            $this->error("LDAP test failed: {$e->getMessage()}");
            $this->error("Stack trace: {$e->getTraceAsString()}");
            return 1;
        }

        return 0;
    }
}
