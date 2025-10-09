<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LdapUserProvider implements UserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     */
    public function retrieveById($identifier)
    {
        return User::find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     */
    public function retrieveByToken($identifier, $token)
    {
        return User::where('id', $identifier)->where('remember_token', $token)->first();
    }

    /**
     * Update the "remember me" token for the given user in storage.
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->setRememberToken($token);
        $user->save();
    }

    /**
     * Retrieve a user by the given credentials.
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials['email'])) {
            return null;
        }

        // First try to find in database
        $user = User::where('email', $credentials['email'])->first();
        
        if ($user) {
            return $user;
        }

        // If not in database, try to find in LDAP
        try {
            $ldapUser = LdapUser::where('mail', $credentials['email'])->first();
            
            if ($ldapUser) {
                // Create user in database
                $user = new User();
                $user->email = $credentials['email'];
                $user->name = $ldapUser->getFirstAttribute('cn') ?? '';
                $user->username = $ldapUser->getFirstAttribute('samaccountname') ?? '';
                $user->first_name = $ldapUser->getFirstAttribute('givenname') ?? '';
                $user->last_name = $ldapUser->getFirstAttribute('sn') ?? '';
                $user->department = $ldapUser->getFirstAttribute('department') ?? '';
                $user->title = $ldapUser->getFirstAttribute('title') ?? '';
                $user->phone = $ldapUser->getFirstAttribute('telephonenumber') ?? '';
                $user->guid = $ldapUser->getFirstAttribute('objectguid');
                $user->password = Hash::make(Str::random(16)); // Random password for LDAP users
                $user->save();
                
                return $user;
            }
        } catch (\Exception $e) {
            \Log::error('LDAP Error in retrieveByCredentials', [
                'email' => $credentials['email'],
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Validate a user against the given credentials.
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        if (empty($credentials['password'])) {
            return false;
        }

        try {
            // Try to bind with LDAP using user credentials
            $ldapUser = LdapUser::where('mail', $credentials['email'])->first();
            
            if ($ldapUser) {
                // Test LDAP bind with user credentials
                $connection = \LdapRecord\Container::getDefaultConnection();
                $connection->connect();
                
                // Try to bind with user credentials
                $result = $connection->auth()->attempt($credentials['email'], $credentials['password']);
                
                return $result;
            }
        } catch (\Exception $e) {
            \Log::error('LDAP Validation Error', [
                'email' => $credentials['email'],
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }
}
