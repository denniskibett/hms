@extends('layouts.app')

@section('content')
    <!-- ===== Main Content Start ===== -->
    <main>
      {{-- <div class="mx-auto max-w-(--breakpoint-2xl) p-4 md:p-6"> --}}
        <!-- Breadcrumb Start -->
        @include('partials.page-header', ['title' => 'Calendar'])
        <!-- Breadcrumb End -->

        <div
          class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3"
        >
          <div id="calendar" class="min-h-screen"></div>
        </div>

        <!-- BEGIN MODAL -->
        @include('partials.calendar-event-modal')
        <!-- END MODAL -->
      {{-- </div> --}}
    </main>
    <!-- ===== Main Content End ===== -->
@endsection
