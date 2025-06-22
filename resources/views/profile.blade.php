@extends('layouts.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #cbd5e0;
            border-radius: 3px;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #4b5563;
        }
        .modal-overlay {
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-colors duration-200">
    <div x-data="{
        isProfileInfoModal: false,
        isProfileAddressModal: false,
        user: {
            firstName: 'Emirhan',
            lastName: 'Boruch',
            email: 'emirhanboruch55@gmail.com',
            phone: '+09 363 398 46',
            bio: 'Team Manager',
            country: 'United Kingdom',
            cityState: 'Leeds, East London',
            postalCode: 'ERT 2489',
            taxId: 'AS4568384',
            social: {
                facebook: 'https://facebook.com/emirhan55',
                twitter: 'https://x.com/emirhan55',
                linkedin: 'https://linkedin.com/emirhan55',
                instagram: 'https://instagram.com/emirhan55'
            }
        },
        tempUser: {},
        openProfileModal() {
            this.tempUser = JSON.parse(JSON.stringify(this.user));
            this.isProfileInfoModal = true;
        },
        openAddressModal() {
            this.tempUser = JSON.parse(JSON.stringify(this.user));
            this.isProfileAddressModal = true;
        },
        saveProfileInfo() {
            this.user = JSON.parse(JSON.stringify(this.tempUser));
            this.isProfileInfoModal = false;
        },
        saveAddressInfo() {
            this.user = JSON.parse(JSON.stringify(this.tempUser));
            this.isProfileAddressModal = false;
        }
    }" class="min-h-screen">
        <!-- Main Content -->
        <main class="mx-auto max-w-screen-2xl p-4 md:p-6">
            <!-- Breadcrumb -->
            <div class="mb-6">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="#" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 dark:text-gray-400 dark:hover:text-white">
                                <i class="fas fa-home mr-2"></i>
                                Home
                            </a>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                                <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2 dark:text-gray-400">Profile</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>

            <!-- Profile Card -->
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-800 lg:p-6">
                <h3 class="mb-5 text-lg font-semibold lg:mb-7">Profile</h3>

                <!-- User Info Section -->
                <div class="mb-6 rounded-2xl border border-gray-200 p-5 dark:border-gray-700 lg:p-6">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                        <div class="flex w-full flex-col items-center gap-6 xl:flex-row">
                            <div class="h-20 w-20 overflow-hidden rounded-full border border-gray-200 dark:border-gray-700">
                                <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=300&q=80" alt="user" class="h-full w-full object-cover">
                            </div>
                            <div class="order-3 xl:order-2">
                                <h4 class="mb-2 text-center text-lg font-semibold xl:text-left" x-text="user.firstName + ' ' + user.lastName"></h4>
                                <div class="flex flex-col items-center gap-1 text-center xl:flex-row xl:gap-3 xl:text-left">
                                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="user.bio"></p>
                                    <div class="hidden h-3.5 w-px bg-gray-300 dark:bg-gray-700 xl:block"></div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="user.cityState"></p>
                                </div>
                            </div>
                            <div class="order-2 flex grow items-center gap-2 xl:order-3 xl:justify-end">
                                <button class="flex h-11 w-11 items-center justify-center gap-2 rounded-full border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                    <i class="fab fa-facebook text-blue-600"></i>
                                </button>
                                <button class="flex h-11 w-11 items-center justify-center gap-2 rounded-full border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                    <i class="fab fa-twitter text-blue-400"></i>
                                </button>
                                <button class="flex h-11 w-11 items-center justify-center gap-2 rounded-full border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                    <i class="fab fa-linkedin text-blue-700"></i>
                                </button>
                                <button class="flex h-11 w-11 items-center justify-center gap-2 rounded-full border border-gray-300 bg-white text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                    <i class="fab fa-instagram text-pink-600"></i>
                                </button>
                            </div>
                        </div>
                        <button @click="openProfileModal()" class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 lg:inline-flex lg:w-auto">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="mb-6 rounded-2xl border border-gray-200 p-5 dark:border-gray-700 lg:p-6">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                        <div class="w-full">
                            <h4 class="text-lg font-semibold lg:mb-6">Personal Information</h4>
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">First Name</p>
                                    <p class="text-sm font-medium" x-text="user.firstName"></p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Last Name</p>
                                    <p class="text-sm font-medium" x-text="user.lastName"></p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Email address</p>
                                    <p class="text-sm font-medium" x-text="user.email"></p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Phone</p>
                                    <p class="text-sm font-medium" x-text="user.phone"></p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Bio</p>
                                    <p class="text-sm font-medium" x-text="user.bio"></p>
                                </div>
                            </div>
                        </div>
                        <button @click="openProfileModal()" class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 lg:inline-flex lg:w-auto">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                </div>
                
                <!-- Address Section -->
                <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-700 lg:p-6">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                        <div class="w-full">
                            <h4 class="text-lg font-semibold lg:mb-6">Address</h4>
                            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Country</p>
                                    <p class="text-sm font-medium" x-text="user.country"></p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">City/State</p>
                                    <p class="text-sm font-medium" x-text="user.cityState"></p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Postal Code</p>
                                    <p class="text-sm font-medium" x-text="user.postalCode"></p>
                                </div>
                                <div>
                                    <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">TAX ID</p>
                                    <p class="text-sm font-medium" x-text="user.taxId"></p>
                                </div>
                            </div>
                        </div>
                        <button @click="openAddressModal()" class="flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 lg:inline-flex lg:w-auto">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Profile Info Modal -->
        <div x-show="isProfileInfoModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-5">
            <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm" @click="isProfileInfoModal = false"></div>
            <div @click.stop class="relative w-full max-w-2xl rounded-2xl bg-white dark:bg-gray-800 shadow-xl overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">Edit Personal Information</h3>
                        <button @click="isProfileInfoModal = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <p class="mb-6 text-gray-600 dark:text-gray-400">Update your details to keep your profile up-to-date.</p>
                    
                    <div class="custom-scrollbar max-h-[60vh] overflow-y-auto pr-2">
                        <!-- Social Links -->
                        <div class="mb-8">
                            <h5 class="mb-4 text-lg font-medium">Social Links</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium">Facebook</label>
                                    <input type="text" x-model="tempUser.social.facebook" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium">X.com</label>
                                    <input type="text" x-model="tempUser.social.twitter" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium">Linkedin</label>
                                    <input type="text" x-model="tempUser.social.linkedin" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium">Instagram</label>
                                    <input type="text" x-model="tempUser.social.instagram" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Personal Information -->
                        <div>
                            <h5 class="mb-4 text-lg font-medium">Personal Information</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block mb-2 text-sm font-medium">First Name</label>
                                    <input type="text" x-model="tempUser.firstName" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium">Last Name</label>
                                    <input type="text" x-model="tempUser.lastName" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium">Email Address</label>
                                    <input type="email" x-model="tempUser.email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block mb-2 text-sm font-medium">Phone</label>
                                    <input type="tel" x-model="tempUser.phone" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block mb-2 text-sm font-medium">Bio</label>
                                    <input type="text" x-model="tempUser.bio" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                    <button @click="isProfileInfoModal = false" type="button" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button @click="saveProfileInfo()" type="button" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>

        <!-- Address Modal -->
        <div x-show="isProfileAddressModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-5">
            <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm" @click="isProfileAddressModal = false"></div>
            <div @click.stop class="relative w-full max-w-2xl rounded-2xl bg-white dark:bg-gray-800 shadow-xl overflow-hidden">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold">Edit Address</h3>
                        <button @click="isProfileAddressModal = false" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <p class="mb-6 text-gray-600 dark:text-gray-400">Update your details to keep your profile up-to-date.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium">Country</label>
                            <input type="text" x-model="tempUser.country" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">City/State</label>
                            <input type="text" x-model="tempUser.cityState" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">Postal Code</label>
                            <input type="text" x-model="tempUser.postalCode" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">TAX ID</label>
                            <input type="text" x-model="tempUser.taxId" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end gap-3">
                    <button @click="isProfileAddressModal = false" type="button" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </button>
                    <button @click="saveAddressInfo()" type="button" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
@endsection