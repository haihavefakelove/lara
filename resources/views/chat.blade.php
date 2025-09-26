@extends('layouts.app')
@section('content')
<div class="max-w-3xl mx-auto p-4">
  <h1 class="text-2xl font-semibold mb-3">Tư vấn mỹ phẩm AI</h1>
  <form id="chat-form" class="mt-3 flex gap-2">
    @csrf
    <input id="msg" class="flex-1 border rounded p-2" placeholder="Mô tả da, nhu cầu, ngân sách…">
    <button class="bg-black text-white px-4 py-2 rounded">Gửi</button>
  </form>
  <div id="chatbox" class="border rounded p-3 h-96 overflow-y-auto text-sm"></div>
  

  <div id="results" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4"></div>
</div>
<script>
const history = [];
const chatbox = document.getElementById('chatbox');
const results = document.getElementById('results');
const form = document.getElementById('chat-form');
const msg = document.getElementById('msg');

function add(role, text){
  const el = document.createElement('div');
  el.className = 'mb-2';
  el.innerHTML = `<b>${role}:</b> ${text}`;
  chatbox.appendChild(el);
  chatbox.scrollTop = chatbox.scrollHeight;
}

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  const text = msg.value.trim(); if (!text) return;
  add('Bạn', text); history.push({ role: 'user', content: text });
  msg.value = '';
  add('AI', 'Đang soạn… / Thinking…');
  const placeholder = chatbox.lastChild;

  try {
    const apiUrl = "{{ route('chat.message') }}";
    console.log('POST =>', apiUrl);

    const r = await fetch(apiUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ message: text, history })
    });

    placeholder.remove();

    if (!r.ok) {
      const errText = await r.text();
      add('AI', `Lỗi ${r.status}: ${errText.slice(0,300)}…`);
      return;
    }

    let data;
    try { data = await r.json(); }
    catch (_) {
      const raw = await r.text();
      add('AI', 'Phản hồi không phải JSON hợp lệ:\n' + raw.slice(0,500) + '…');
      return;
    }

    add('AI', (data.advisor?.message) || '(no message)');
    if (data.advisor?.follow_up_questions?.length) {
      add('AI', 'Câu hỏi thêm: ' + data.advisor.follow_up_questions.join(' | '));
    }

    results.innerHTML = '';
    (data.products || []).forEach(p => {
      const card = document.createElement('div');
      card.className = 'border rounded p-3';
      card.innerHTML = `
        <img src="/${p.image_url || ''}" alt="${p.name || ''}" class="w-full h-40 object-cover rounded mb-2">
        <div class="font-semibold">${p.name || ''}</div>
        <div class="text-xs text-gray-600">${p.brand || ''} · ${p.origin || ''}</div>
        <div class="mt-1"><b>${Number(p.price || 0).toLocaleString()} đ</b></div>
        <div class="text-xs mt-1">${p.features || ''}</div>
        <a href="/products/${p.id}" class="inline-block mt-2 text-blue-600">Xem chi tiết</a>
      `;
      results.appendChild(card);
    });

    history.push({ role: 'assistant', content: JSON.stringify(data.advisor) });

  } catch (err) {
    try { placeholder.remove(); } catch {}
    add('AI', 'Kết nối thất bại: ' + (err?.message || err));
  }
});
</script>

@endsection
