<?php
namespace App\Models;

use Illuminate\Support\Facades\Hash;

class Helpers
{
    public function validateUserPassword()
    {
        if (Hash::check("param1", "param2")) {
            //add logic here
           }

        //    param1 - user password that has been entered on the form
        //    param2 - old password hash stored in database

    }

    public function dateToHumanFormat($date)
    {
        return date("F jS, Y", strtotime($date));
    }

}
