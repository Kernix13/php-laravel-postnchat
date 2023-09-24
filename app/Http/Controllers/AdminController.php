<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function adminsOnly() {
        return 'Testing AdminController: admins only here';
    }
}
