@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3 class="mb-0">Trang tĩnh</h3>
  <a href="{{ route('admin.pages.create') }}" class="btn btn-primary">Tạo trang</a>
</div>

@if(session('success'))
  <div class="alert alert-success">{{ session('success') }}</div>
@endif

<table class="table table-striped align-middle">
  <thead>
    <tr>
      <th>Tiêu đề</th>
      <th>Slug</th>
      <th>Trạng thái</th>
      <th>Cập nhật</th>
      <th class="text-end">Thao tác</th>
    </tr>
  </thead>
  <tbody>
    @forelse($pages as $p)
      <tr>
        <td>{{ $p->title }}</td>
        <td><code>/page/{{ $p->slug }}</code></td>
        <td>
          <span class="badge {{ $p->status=='published'?'bg-success':'bg-secondary' }}">
            {{ $p->status }}
          </span>
        </td>
        <td>{{ $p->updated_at->format('d/m/Y H:i') }}</td>
        <td class="text-end">
          <a href="{{ route('admin.pages.edit',$p) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
          <form action="{{ route('admin.pages.destroy',$p) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Xóa trang này?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">Xóa</button>
          </form>
          <a href="{{ route('page.show',$p->slug) }}" class="btn btn-sm btn-outline-secondary" target="_blank">Xem</a>
        </td>
      </tr>
    @empty
      <tr><td colspan="5" class="text-muted">Chưa có trang.</td></tr>
    @endforelse
  </tbody>
</table>

{{ $pages->links() }}
@endsection
