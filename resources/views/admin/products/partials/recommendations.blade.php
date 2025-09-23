<div class="mt-4">
  <h3 class="h5 mb-3">Gợi ý cho bạn</h3>
  <div class="row g-3">
    @forelse($products as $p)
      <div class="col-6 col-md-3">
        <a href="{{ route('products.show', $p->id) }}" class="text-decoration-none">
          <div class="card h-100">
            @if(!empty($p->image_url))
              <img src="{{ $p->image_url }}" class="card-img-top" alt="{{ $p->name }}">
            @endif
            <div class="card-body">
              <div class="fw-semibold text-truncate">{{ $p->name }}</div>
              @isset($p->price)
                <div class="text-danger mt-1">{{ number_format($p->price) }} đ</div>
              @endisset
            </div>
          </div>
        </a>
      </div>
    @empty
      <p>Chưa có gợi ý phù hợp.</p>
    @endforelse
  </div>
</div>
