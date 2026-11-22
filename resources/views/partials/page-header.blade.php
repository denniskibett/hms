{{--
    Gives this page's title (and an optional line under it, and crumbs) to the header bar the layout draws:
        @include('partials.page-header', ['title' => 'Rooms', 'sub' => 'Room inventory and status', 'crumbs' => ['Finance' => route('finance.index')]])
    Without it the layout takes the title and crumbs from the address.
--}}
@php
    \App\Support\PageHeader::set(['title' => $title ?? null, 'sub' => $sub ?? null, 'crumbs' => $crumbs ?? null]);
@endphp
