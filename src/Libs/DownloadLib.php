<?php

namespace App\Libs;

class DownloadLib
{
    public static function createLink($file, $folder){

        $jwt = new JwtLib();
        $token = $jwt->encode(10);
        return "/download/?file=$file&folder={$folder}&token={$token}";

    }
}