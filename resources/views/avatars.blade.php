@extends('layouts.app')

@section('content')
        <!-- ===== Main Content Start ===== -->
        <main>
          <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Avatars`}">
              @include('partials.breadcrumb')
            </div>
            <!-- Breadcrumb End -->

            <div class="space-y-5 sm:space-y-6">
              <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
              >
                <div class="px-6 py-5">
                  <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90"
                  >
                    Default Avatar
                  </h3>
                </div>
                <div class="p-8 border-t border-gray-100 dark:border-gray-800">
                  @include('partials.avatar.avatar-01')
                </div>
              </div>

              <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
              >
                <div class="px-6 py-5">
                  <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90"
                  >
                    Avatar with online indicator
                  </h3>
                </div>
                <div class="p-8 border-t border-gray-100 dark:border-gray-800">
                  @include('partials.avatar.avatar-02')

                </div>
              </div>

              <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
              >
                <div class="px-6 py-5">
                  <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90"
                  >
                    Avatar with Offline indicator
                  </h3>
                </div>
                <div class="p-8 border-t border-gray-100 dark:border-gray-800">
                  @include('partials.avatar.avatar-03')
                </div>
              </div>

              <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
              >
                <div class="px-6 py-5">
                  <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90"
                  >
                    Avatar with busy indicator
                  </h3>
                </div>
                <div class="p-8 border-t border-gray-100 dark:border-gray-800">
                  @include('partials.avatar.avatar-04')
                </div>
              </div>
            </div>
          </div>
        </main>
        <!-- ===== Main Content End ===== -->
@endsection