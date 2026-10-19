<?php
namespace App\Http\Controllers;

use App\Helpers\SystemHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    public function submitContact(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:5000',
        ]);

        try {
            Mail::raw("From: {$data['name']} <{$data['email']}>\n\n{$data['message']}", function ($mail) use ($data) {
                $mail->to(SystemHelper::contactEmail() ?: config('mail.from.address'))
                    ->replyTo($data['email'], $data['name'])
                    ->subject('Website enquiry from ' . $data['name']);
            });
        } catch (\Throwable $e) {
            Log::error('Contact form mail failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Sorry, we could not send your message. Please try again later.');
        }

        return back()->with('success', 'Thank you! We will get back to you shortly.');
    }
}
