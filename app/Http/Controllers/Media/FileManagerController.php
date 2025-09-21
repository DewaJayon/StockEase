<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FileManagerController extends Controller
{
    public function index()
    {
        return Inertia::render('FileManager/Index');
    }
}
