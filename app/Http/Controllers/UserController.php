<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;
use App\User;
use App\UserPhoto;
use Image;

class UserController extends Controller
{
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->res['message'] = '';
        $this->status_code = 204;
        date_default_timezone_set('America/Mexico_City');
    }


}
