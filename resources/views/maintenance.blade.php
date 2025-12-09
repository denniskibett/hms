<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - {{ SystemHelper::appName() }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: {{ SystemHelper::primaryColor() }};
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <img src="{{ SystemHelper::logoUrl() }}" alt="Logo" class="h-16 mx-auto">
            </div>
            
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-yellow-100 flex items-center justify-center">
                    <svg class="w-10 h-10 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                
                <h1 class="text-2xl font-bold text-gray-800 mb-3">Under Maintenance</h1>
                <p class="text-gray-600 mb-6">
                    {{ SystemHelper::appName() }} is currently undergoing scheduled maintenance. We'll be back online shortly.
                </p>
                
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">
                        Estimated time: <span class="font-medium">30 minutes</span><br>
                        Started at: <span class="font-medium">{{ now()->format('g:i A') }}</span>
                    </p>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-500">
                        Need help? Contact us at:<br>
                        <a href="mailto:{{ SystemHelper::get('contact_email') }}" class="text-primary hover:underline">
                            {{ SystemHelper::get('contact_email') }}
                        </a>
                    </p>
                </div>
            </div>
            
            <p class="mt-6 text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ SystemHelper::appName() }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>