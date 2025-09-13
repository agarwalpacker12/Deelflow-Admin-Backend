<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\MockableController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;

class UserRolePermissionController extends Controller
{
    use MockableController;

    public function __construct()
    {
        $this->initializeMockDataService();
    }
    
}
