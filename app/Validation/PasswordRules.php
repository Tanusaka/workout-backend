<?php

namespace App\Validation;

use App\Models\Core\UserModel;

class PasswordRules
{
    public function check_current_password(string $str, string $fields = NULL, array $data = NULL, string &$error = NULL): bool
    {
        if ( !isset($data['userid']) ) {
            $error = "Current Password is invalid";
            return false;
        } 

        $usermodel = new UserModel();
        $current_password_hash = $usermodel->fetchPassword($data['userid']);
    
        if( !password_verify($str, $current_password_hash) ) {
            $error = "Current Password is invalid";
            return false;
        } 

        return true;
    }
}