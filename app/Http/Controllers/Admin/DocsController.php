<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DocsController extends Controller
{
    /**
     * Display the API documentation page.
     */
    public function api()
    {
        return view('admin.docs.api');
    }
}
