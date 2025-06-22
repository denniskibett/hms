@extends('layouts.app')

@section('content')
        <!-- ===== Main Content Start ===== -->
        <main>
          <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Videos`}">
              @include('partials.breadcrumb')
            </div>
            <!-- Breadcrumb End -->
            <div class="grid grid-cols-1 gap-5 sm:gap-6 xl:grid-cols-2">
              <div class="space-y-5 sm:space-y-6">
                <div
                  class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                >
                  <div
                    class="px-6 py-5 border-b border-gray-200 dark:border-gray-800"
                  >
                    <h3
                      class="text-base font-medium text-gray-800 dark:text-white/90"
                    >
                      Video Ratio 16:9
                    </h3>
                  </div>
                  <div class="p-4 sm:p-6">
                    @include('partials.video.video-01')
                  </div>
                </div>
                <div
                  class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                >
                  <div
                    class="px-6 py-5 border-b border-gray-200 dark:border-gray-800"
                  >
                    <h3
                      class="text-base font-medium text-gray-800 dark:text-white/90"
                    >
                      Video Ratio 4:3
                    </h3>
                  </div>
                  <div class="p-4 sm:p-6">
                    @include('partials.video.video-02')
                  </div>
                </div>
              </div>
              <div class="space-y-5 sm:space-y-6">
                <div
                  class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                >
                  <div
                    class="px-6 py-5 border-b border-gray-200 dark:border-gray-800"
                  >
                    <h3
                      class="text-base font-medium text-gray-800 dark:text-white/90"
                    >
                      Video Ratio 4:3
                    </h3>
                  </div>
                  <div class="p-4 sm:p-6">
                    @include('partials.video.video-03')
                  </div>
                </div>
                <div
                  class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
                >
                  <div
                    class="px-6 py-5 border-b border-gray-200 dark:border-gray-800"
                  >
                    <h3
                      class="text-base font-medium text-gray-800 dark:text-white/90"
                    >
                      Video Ratio 1:1
                    </h3>
                  </div>
                  <div class="p-4 sm:p-6">
                    @include('partials.video.video-04')
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
        <!-- ===== Main Content End ===== -->
@endsection