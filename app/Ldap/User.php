<?php

namespace App\Ldap;

use LdapRecord\Models\Model;

class User extends Model
{
    /**
     * The object classes of the LDAP model.
     */
    public static array $objectClasses = [
        'top',
        'person',
        'organizationalperson',
        'user',
    ];

    /**
     * The attributes that should be mutated to dates.
     */
    protected array $dates = [
        'whenchanged',
        'whencreated',
        'lastlogon',
        'lastlogontimestamp',
        'pwdlastset',
        'lockouttime',
        'accountexpires',
    ];

    /**
     * Get the user's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->getFirstAttribute('displayname') ?: $this->getFirstAttribute('cn');
    }

    /**
     * Get the user's email address.
     */
    public function getEmailAttribute(): string
    {
        return $this->getFirstAttribute('mail') ?: '';
    }

    /**
     * Get the user's username.
     */
    public function getUsernameAttribute(): string
    {
        return $this->getFirstAttribute('samaccountname') ?: $this->getFirstAttribute('uid');
    }

    /**
     * Get the user's first name.
     */
    public function getFirstNameAttribute(): string
    {
        return $this->getFirstAttribute('givenname') ?: '';
    }

    /**
     * Get the user's last name.
     */
    public function getLastNameAttribute(): string
    {
        return $this->getFirstAttribute('sn') ?: '';
    }

    /**
     * Get the user's department.
     */
    public function getDepartmentAttribute(): string
    {
        return $this->getFirstAttribute('department') ?: '';
    }

    /**
     * Get the user's title.
     */
    public function getTitleAttribute(): string
    {
        return $this->getFirstAttribute('title') ?: '';
    }

    /**
     * Get the user's phone number.
     */
    public function getPhoneAttribute(): string
    {
        return $this->getFirstAttribute('telephonenumber') ?: '';
    }

    /**
     * Check if the user account is enabled.
     */
    public function isEnabled(): bool
    {
        $userAccountControl = $this->getFirstAttribute('useraccountcontrol');
        
        if (!$userAccountControl) {
            return true;
        }

        // Check if the ACCOUNTDISABLE flag (0x2) is not set
        return !($userAccountControl & 2);
    }

    /**
     * Check if the user account is locked.
     */
    public function isLocked(): bool
    {
        $lockoutTime = $this->getFirstAttribute('lockouttime');
        
        return $lockoutTime && $lockoutTime > 0;
    }
}
