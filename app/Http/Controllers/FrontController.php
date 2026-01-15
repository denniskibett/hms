<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontController extends Controller
{
    public function about()
    {
        return view('about-us'); // Blade file: resources/views/pages/about.blade.php
    }    
    
    public function contact()
    {
        return view('contact'); // Blade file: resources/views/pages/about.blade.php
    }
}
