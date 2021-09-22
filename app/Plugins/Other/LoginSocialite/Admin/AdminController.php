<?php
#App\Plugins\Other\LoginSocialite\Admin\AdminController.php

namespace App\Plugins\Other\LoginSocialite\Admin;

use App\Http\Controllers\RootAdminController;
use App\Plugins\Other\LoginSocialite\AppConfig;

class AdminController extends RootAdminController
{
    public $plugin;

    public function __construct()
    {
        $this->plugin = new AppConfig;
    }
    public function index()
    {
        return view($this->plugin->pathPlugin.'::Admin',
            [
                
            ]
        );
    }
}
