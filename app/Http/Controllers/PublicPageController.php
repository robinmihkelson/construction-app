<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicPageController extends Controller
{
    public function home()
    {
        return Inertia::render('Public/Home');
    }

    public function services()
    {
        return Inertia::render('Public/Services');
    }

    public function portfolio()
    {
        return Inertia::render('Public/Portfolio');
    }

    public function contact()
    {
        return Inertia::render('Public/Contact');
    }
}
