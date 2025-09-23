@extends('layouts.app')
@section('content')
<h3 class="mb-3">Sửa trang: {{ $page->title }}</h3>
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if($errors->any())
  <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<form method="POST" action="{{ route('admin.pages.update',$page) }}">
  @method('PUT')
  @include('admin.pages._form')
</form>
@endsection
