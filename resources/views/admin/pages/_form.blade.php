@csrf
<div class="mb-3">
  <label class="form-label">Tiêu đề</label>
  <input type="text" name="title" class="form-control" value="{{ old('title',$page->title) }}" required>
</div>
<div class="mb-3">
  <label class="form-label">Slug (URL)</label>
  <input type="text" name="slug" class="form-control" value="{{ old('slug',$page->slug) }}" placeholder="vd: ve-chung-toi">
  <div class="form-text">Để trống để tự tạo từ tiêu đề.</div>
</div>
<div class="mb-3">
  <label class="form-label">Nội dung (HTML)</label>
  <textarea name="content" rows="12" class="form-control">{{ old('content',$page->content) }}</textarea>
</div>
<div class="row">
  <div class="col-md-4 mb-3">
    <label class="form-label">Trạng thái</label>
    <select name="status" class="form-select">
      <option value="draft" {{ old('status',$page->status)=='draft'?'selected':'' }}>Nháp</option>
      <option value="published" {{ old('status',$page->status)=='published'?'selected':'' }}>Công khai</option>
    </select>
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Ngày xuất bản</label>
    <input type="datetime-local" name="published_at" class="form-control"
           value="{{ old('published_at', optional($page->published_at)->format('Y-m-d\TH:i')) }}">
  </div>
</div>
<div class="mb-3">
  <label class="form-label">Meta title</label>
  <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title',$page->meta_title) }}">
</div>
<div class="mb-3">
  <label class="form-label">Meta description</label>
  <textarea name="meta_description" rows="2" class="form-control">{{ old('meta_description',$page->meta_description) }}</textarea>
</div>
<button type="submit" class="btn btn-primary">Lưu</button>
<a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">Hủy</a>
