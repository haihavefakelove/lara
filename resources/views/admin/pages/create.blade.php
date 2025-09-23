@extends('layouts.app')
@section('content')
<h3 class="mb-3">Tạo trang</h3>
@if($errors->any())
  <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
@endif
<form method="POST" action="{{ route('admin.pages.store') }}">
  @include('admin.pages._form')
</form>
@endsection
