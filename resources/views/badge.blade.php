@extends('layouts.app')

@section('content')
        <!-- ===== Main Content Start ===== -->
        <main>
          <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Badge`}">
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
                    With Light Background
                  </h3>
                </div>
                <div
                  class="border-t border-gray-100 p-6 dark:border-gray-800 xl:p-10"
                >
                  @include('partials.badge.badge-01')
                </div>
              </div>

              <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
              >
                <div class="px-6 py-5">
                  <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90"
                  >
                    With Solid Background
                  </h3>
                </div>
                <div
                  class="border-t border-gray-100 p-6 dark:border-gray-800 xl:p-10"
                >
                  @include('partials.badge.badge-02')
                </div>
              </div>

              <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
              >
                <div class="px-6 py-5">
                  <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90"
                  >
                    Light Background with Left Icon
                  </h3>
                </div>
                <div
                  class="border-t border-gray-100 p-6 dark:border-gray-800 xl:p-10"
                >
                  @include('partials.badge.badge-03')
                </div>
              </div>

              <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
              >
                <div class="px-6 py-5">
                  <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90"
                  >
                    Solid Background with Left Icon
                  </h3>
                </div>
                <div
                  class="border-t border-gray-100 p-6 dark:border-gray-800 xl:p-10"
                >
                  @include('partials.badge.badge-04')
                </div>
              </div>

              <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
              >
                <div class="px-6 py-5">
                  <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90"
                  >
                    Light Background with Right Icon
                  </h3>
                </div>
                <div
                  class="border-t border-gray-100 p-6 dark:border-gray-800 xl:p-10"
                >
                  @include('partials.badge.badge-05')
                </div>
              </div>

              <div
                class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
              >
                <div class="px-6 py-5">
                  <h3
                    class="text-base font-medium text-gray-800 dark:text-white/90"
                  >
                    Solid Background with Right Icon
                  </h3>
                </div>
                <div
                  class="border-t border-gray-100 p-6 dark:border-gray-800 xl:p-10"
                >
                  @include('partials.badge.badge-06')
                </div>
              </div>
            </div>
          </div>
        </main>
        <!-- ===== Main Content End ===== -->
@endsection