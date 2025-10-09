<?php

namespace App\Ldap\Rules;

use LdapRecord\Laravel\Auth\Rule;
use LdapRecord\Models\Model as LdapRecord;
use Illuminate\Database\Eloquent\Model as Eloquent;

class OnlyHelpDeskUsers implements Rule
{
    /**
     * Check if the rule passes validation.
     */
    public function passes(LdapRecord $user, ?Eloquent $model = null): bool
    {
        // Si no hay usuario, no es válido
        if (!$user) {
            return false;
        }

        // Verificar que el usuario esté habilitado
        if ($user->getFirstAttribute('userAccountControl')) {
            $uac = $user->getFirstAttribute('userAccountControl');
            if ($uac & 2) { // ACCOUNTDISABLE flag
                return false;
            }
        }

        // Verificar que tenga un email válido
        if (!$user->getFirstAttribute('mail')) {
            return false;
        }

        // Verificar que tenga un nombre de usuario
        if (!$user->getFirstAttribute('samaccountname')) {
            return false;
        }

        return true;
    }
}
