@extends('layouts.app')

@section('content')
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `Guest Management`}">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->
    @include('partials.card.card-guests')
    @include('partials.table.table-guests')
    
@endsection