<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ $page->title }}</h2>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="h3 mb-4">{{ $page->title }}</h1>
                        <p class="text-muted">Last updated: {{ $page->updated_at->format('F d, Y') }}</p>

                        <div class="content mt-4">
                            {!! $page->content !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 