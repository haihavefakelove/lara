{{-- resources/views/pages/show.blade.php --}}
@extends('layouts.app')

{{-- Nếu layout của bạn không @yield('title') thì có thể bỏ dòng dưới --}}
@section('title', $page->meta_title ?: $page->title)

@section('content')
<div class="card shadow-sm">
  <div class="card-body">
    <h1 class="h4 mb-3">{{ $page->title }}</h1>

    {{-- Nội dung HTML do admin nhập --}}
    <article class="content">
      {!! $page->content !!}
    </article>

    @if($page->updated_at)
      <div class="text-muted small mt-3">
        Cập nhật: {{ $page->updated_at->format('d/m/Y H:i') }}
      </div>
    @endif
  </div>
</div>
@endsection
