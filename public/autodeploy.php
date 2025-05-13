<?php
/**
 * Created by PhpStorm.
 * User: jp
 * Date: 1/4/19
 * Time: 11:18 AM
 */

$password = "T4397FordHenry@";

$salt = "AoudhfaiusgIU#847874382iURIBFSIBibdbsiubf__--9w86324#$*76bfskjbdf**&#4lvnbwiuGbfoouffghOEUHiUGrGF&*¨499HKHk--__fUCKaLL";

if($_GET['flag'] == hash('sha256', $salt.$password)) {
    /**
     * &1 means value reference to file descriptor 1 stdout
     * 2>&1 saying Redirect the stderr to the same place we are redirecting the stdout
     */
    $output = shell_exec('git reset --hard && git pull origin main 2>&1');

    echo "<pre>$output</pre>";
}

//https://dominio.com/autodeploy.php?flag=98c940c5c2071b489f60660f3a1cb9baa3c117fda8acd1c9a8a4d59f50d63da3