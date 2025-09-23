@php
    $cfg    = config('services.tawk');
    $enable = (bool) ($cfg['enabled'] ?? false);

    // Mặc định ẩn trên các trang admin để UI gọn gàng
    $isAdminArea = request()->is('admin') || request()->is('admin/*');

    // Tạo embed URL từ config
    $embed = $cfg['embed_url']
        ?? ( ($cfg['property_id'] ?? null) && ($cfg['widget_id'] ?? null)
             ? 'https://embed.tawk.to/' . $cfg['property_id'] . '/' . $cfg['widget_id']
             : null );

    // Có thể tuỳ ý giới hạn theo môi trường:
    // $onlyOnProd = app()->environment('production');
@endphp

@if ($enable && !$isAdminArea && $embed)
    <!-- Start of Tawk.to Script -->
    <script type="text/javascript">
      window.Tawk_API = window.Tawk_API || {};
      window.Tawk_LoadStart = new Date();

      (function() {
        var s1 = document.createElement("script"),
            s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = @json($embed);
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
      })();

      @if(auth()->check())
        // Điền sẵn thông tin khách (giúp agent biết ai đang chat)
        Tawk_API = Tawk_API || {};
        Tawk_API.onLoad = function () {
          try {
            Tawk_API.setAttributes({
              'name'  : @json(auth()->user()->name ?? 'Guest'),
              'email' : @json(auth()->user()->email ?? null)
              // 'hash' : '{{ config('services.tawk.sso_key') ? hash_hmac('sha256', auth()->user()->email, config('services.tawk.sso_key')) : '' }}'
            }, function(err) { /* ignore */ });
          } catch (e) {}
        };
      @endif
    </script>
    <!-- End of Tawk.to Script -->
@endif
