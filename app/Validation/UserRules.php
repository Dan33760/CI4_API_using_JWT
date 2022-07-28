<?php

namespace App\Validation;

use App\Models\UserModel;

class UserRules
{
    //-- Verifier si l'adresse mail existe dans le db
    public function validateUser(string $str, string $fields, array $data)
    {
        $userModel = new UserModel();

        $user = $userModel->where('EMAIL_USER', $data['email'])->first();
        if(!$user) {
            return false;
        }

        if($user['ETAT_USER'] != 1)
        {
            return false;
        }

        return password_verify($data['password'], $user['MDP_USER']);

    }
}
