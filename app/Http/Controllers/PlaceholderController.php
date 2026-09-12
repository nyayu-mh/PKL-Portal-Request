<?php

namespace App\Http\Controllers;

class PlaceholderController extends Controller
{
    public function show(string $title, string $description)
    {
        return view('placeholder', compact('title', 'description'));
    }
}
