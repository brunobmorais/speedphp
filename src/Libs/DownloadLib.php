<?php

namespace App\Libs;

class DownloadLib
{
    public static function createLink($file, $folder, $minutes = 10){

        $jwt = new JwtLib();
        $token = $jwt->encode($minutes);
        return "/documento/download/?file=$file&folder={$folder}&token={$token}";

    }
}