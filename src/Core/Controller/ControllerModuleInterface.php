<?php

namespace App\Core\Controller;

interface ControllerModuleInterface
{
    public function index(array $args = []);

    public function action(array $args = []);


}