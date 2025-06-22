@extends('layouts.app')

@section('content')
        <!-- ===== Main Content Start ===== -->
        <main>
          {{-- <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6"> --}}
            <!-- Breadcrumb Start -->
            <div x-data="{ pageName: `Basic Tables`}">
              @include('partials.breadcrumb')
            </div>
            <!-- Breadcrumb End -->

            <div class="space-y-5 sm:space-y-6">
              <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="px-5 py-4 sm:px-6 sm:py-5">
                  <h3 class="text-base font-medium text-gray-800 dark:text-white/90">
                    Basic Table 1
                  </h3>
                </div>
                <div class="p-5 border-t border-gray-100 dark:border-gray-800 sm:p-6" >
                  <!-- ====== Table Six Start -->
                  @include('partials.table.table-06')

                  <!-- ====== Table Six End -->
                </div>
              </div>
            </div>
          {{-- </div> --}}
        </main>
        <!-- ===== Main Content End ===== -->
@endsection
