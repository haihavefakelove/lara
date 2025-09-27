
<div id="ai-chat-popup" class="ai-chat-popup">
    
    <div id="chat-toggle-btn" class="chat-toggle-btn">
        <i class="bi bi-chat-dots-fill"></i>
        <span class="chat-tooltip">Tư vấn AI</span>
    </div>


    <div id="chat-window" class="chat-window">
        
        <div class="chat-panel">
            <div class="chat-header">
                <div class="chat-title">
                    <i class="bi bi-robot me-2"></i>
                    Tư vấn AI
                </div>
                <div class="chat-controls">
                    <button id="chat-clear-btn" class="chat-clear-btn" title="Xóa lịch sử chat">
                        <i class="bi bi-trash3"></i>
                    </button>
                    <button id="chat-close-btn" class="chat-close-btn">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            
            <div id="chat-messages" class="chat-messages">
                <div class="chat-message ai-message">
                    <div class="message-content">
                        <strong>AI:</strong> Xin chào! Tôi có thể giúp bạn tư vấn mỹ phẩm phù hợp. Hãy mô tả loại da, nhu cầu và ngân sách của bạn nhé!
                    </div>
                </div>
            </div>
            
            <div class="chat-input-container">
                <form id="popup-chat-form" class="chat-form">
                    @csrf
                    <div class="input-group">
                        <input type="text" id="popup-chat-input" class="form-control" 
                               placeholder="Mô tả da, nhu cầu, ngân sách…" required>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        

        <div class="products-panel">
            <div class="products-header">
                <i class="bi bi-bag-heart me-2"></i>
                <span>Sản phẩm gợi ý</span>
            </div>
            <div id="popup-chat-results" class="chat-results">
                <div class="no-products">
                    <i class="bi bi-search"></i>
                    <p>Sản phẩm gợi ý sẽ xuất hiện ở đây sau khi bạn chat với AI</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ai-chat-popup {
    position: fixed;
    bottom: 20px;
    left: 20px;
    z-index: 9999;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.chat-toggle-btn {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
    position: relative;
}

.chat-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
}

.chat-toggle-btn i {
    color: white;
    font-size: 24px;
}

.chat-tooltip {
    position: absolute;
    left: 70px;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.chat-toggle-btn:hover .chat-tooltip {
    opacity: 1;
    visibility: visible;
}

.chat-window {
    position: absolute;
    bottom: 70px;
    left: 0;
    width: 700px;
    height: 580px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    display: none;
    flex-direction: row;
    overflow: hidden;
}

.chat-window.active {
    display: flex;
}


.chat-panel {
    width: 60%;
    display: flex;
    flex-direction: column;
    border-right: 1px solid #e9ecef;
}

.chat-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 54px;
    box-sizing: border-box;
}

.chat-title {
    font-weight: 600;
    font-size: 15px;
}

.chat-controls {
    display: flex;
    gap: 5px;
}

.chat-close-btn, .chat-clear-btn {
    background: none;
    border: none;
    color: white;
    font-size: 16px;
    cursor: pointer;
    padding: 5px;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.chat-close-btn:hover, .chat-clear-btn:hover {
    background: rgba(255, 255, 255, 0.2);
}

.chat-clear-btn {
    font-size: 14px;
}

.chat-messages {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    max-height: calc(580px - 140px);
}

.products-panel {
    width: 40%;
    display: flex;
    flex-direction: column;
    background: #f8f9fa;
    transition: opacity 0.3s ease;
}

.products-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    font-weight: 600;
    font-size: 15px;
    display: flex;
    align-items: center;
    height: 54px;
    box-sizing: border-box;
}

.chat-message {
    margin-bottom: 15px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message-content {
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 14px;
    line-height: 1.4;
}

.ai-message .message-content {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
}

.user-message .message-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    margin-left: 20px;
}

.chat-input-container {
    padding: 15px;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}

.chat-form .input-group input {
    border-radius: 20px 0 0 20px;
    border: 1px solid #ddd;
    padding: 8px 12px;
    font-size: 14px;
}

.chat-form .input-group button {
    border-radius: 0 20px 20px 0;
    padding: 8px 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.chat-results {
    flex: 1;
    overflow-y: auto;
    padding: 15px;
}

.no-products {
    text-align: center;
    color: #6c757d;
    padding: 40px 20px;
}

.no-products i {
    font-size: 32px;
    margin-bottom: 15px;
    display: block;
}

.no-products p {
    font-size: 13px;
    line-height: 1.4;
    margin: 0;
}

.product-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 10px;
    margin-bottom: 10px;
    transition: all 0.2s ease;
    animation: slideInProduct 0.4s ease forwards;
    opacity: 0;
    transform: translateX(20px);
}

.product-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transform: translateY(-1px);
}

.product-card img {
    width: 100%;
    height: 60px;
    object-fit: cover;
    border-radius: 4px;
    margin-bottom: 6px;
}

.product-name {
    font-weight: 600;
    font-size: 12px;
    margin-bottom: 3px;
    line-height: 1.3;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-brand {
    font-size: 10px;
    color: #666;
    margin-bottom: 3px;
}

.product-price {
    font-weight: 600;
    color: #28a745;
    font-size: 12px;
    margin-bottom: 4px;
}

.product-features {
    font-size: 10px;
    color: #666;
    margin-bottom: 6px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.2;
}

.product-link {
    font-size: 11px;
    color: #007bff;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.product-link:hover {
    text-decoration: underline;
}


@media (max-width: 768px) {
    .ai-chat-popup {
        bottom: 15px;
        left: 15px;
    }
    
    .chat-window {
        width: calc(100vw - 30px);
        height: 70vh;
        left: -10px;
        bottom: 75px;
        flex-direction: column;
    }
    
    .chat-panel {
        width: 100%;
        height: 60%;
        border-right: none;
        border-bottom: 1px solid #e9ecef;
    }
    
    .products-panel {
        width: 100%;
        height: 40%;
    }
    
    .chat-toggle-btn {
        width: 55px;
        height: 55px;
    }
    
    .chat-toggle-btn i {
        font-size: 22px;
    }
    
    .chat-tooltip {
        display: none;
    }
    
    .chat-messages {
        max-height: calc(42vh - 140px);
    }
    
    .chat-results {
        max-height: calc(28vh - 50px);
    }
}

@media (max-width: 480px) {
    .chat-window {
        width: calc(100vw - 20px);
        left: -5px;
        height: 75vh;
    }
    
    .chat-panel {
        height: 65%;
    }
    
    .products-panel {
        height: 35%;
    }
    
    .chat-header, .products-header {
        padding: 12px 15px;
    }
    
    .chat-title {
        font-size: 14px;
    }
    
    .chat-messages {
        padding: 12px;
        max-height: calc(49vh - 130px);
    }
    
    .chat-input-container {
        padding: 12px;
    }
    
    .chat-results {
        padding: 12px;
        max-height: calc(26vh - 50px);
    }
}


.typing-indicator {
    display: flex;
    align-items: center;
    padding: 10px 12px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 15px;
}

.typing-indicator span {
    height: 8px;
    width: 8px;
    background: #999;
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
    animation: typing 1.4s infinite ease-in-out;
}

.typing-indicator span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
        opacity: 0.5;
    }
    30% {
        transform: translateY(-10px);
        opacity: 1;
    }
}

@keyframes slideInProduct {
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>

<!-- <link rel="stylesheet" href="{{ asset('css/popup.css') }}"> -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load chat history from localStorage
    let chatHistory = [];
    try {
        const savedHistory = localStorage.getItem('aiChatHistory');
        if (savedHistory) {
            chatHistory = JSON.parse(savedHistory);
        }
    } catch (e) {
        console.log('Failed to load chat history:', e);
        chatHistory = [];
    }
    
    const chatToggleBtn = document.getElementById('chat-toggle-btn');
    const chatWindow = document.getElementById('chat-window');
    const chatCloseBtn = document.getElementById('chat-close-btn');
    const chatClearBtn = document.getElementById('chat-clear-btn');
    const chatMessages = document.getElementById('chat-messages');
    const chatForm = document.getElementById('popup-chat-form');
    const chatInput = document.getElementById('popup-chat-input');
    const chatResults = document.getElementById('popup-chat-results');


    chatToggleBtn.addEventListener('click', function() {
        chatWindow.classList.add('active');
        chatInput.focus();

        localStorage.setItem('chatPopupOpen', 'true');
    });

    chatCloseBtn.addEventListener('click', function() {
        chatWindow.classList.remove('active');
        localStorage.setItem('chatPopupOpen', 'false');
    });

    chatClearBtn.addEventListener('click', function() {
        if (confirm('Bạn có chắc muốn xóa toàn bộ lịch sử chat không?')) {
            chatHistory.length = 0; 
            localStorage.removeItem('aiChatHistory');
            

            localStorage.removeItem('aiLastProducts');
            chatResults.innerHTML = `
                <div class="no-products">
                    <i class="bi bi-search"></i>
                    <p>Sản phẩm gợi ý sẽ xuất hiện ở đây sau khi bạn chat với AI</p>
                </div>
            `;
            
            restoreChatMessages();
        }
    });


    document.addEventListener('click', function(e) {
        if (!e.target.closest('.ai-chat-popup')) {
            chatWindow.classList.remove('active');
            localStorage.setItem('chatPopupOpen', 'false');
        }
    });


    document.addEventListener('keydown', function(e) {

        if (e.key === 'Escape' && chatWindow.classList.contains('active')) {
            chatWindow.classList.remove('active');
            localStorage.setItem('chatPopupOpen', 'false');
        }

        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            if (chatWindow.classList.contains('active')) {
                chatWindow.classList.remove('active');
                localStorage.setItem('chatPopupOpen', 'false');
            } else {
                chatWindow.classList.add('active');
                chatInput.focus();
                localStorage.setItem('chatPopupOpen', 'true');
            }
        }
    });


    function saveChatHistory() {
        try {
            localStorage.setItem('aiChatHistory', JSON.stringify(chatHistory));
        } catch (e) {
            console.log('Failed to save chat history:', e);
        }
    }
    function saveLastProducts(products) {
        try {
            localStorage.setItem('aiLastProducts', JSON.stringify(products));
        } catch (e) {
            console.log('Failed to save products:', e);
        }
    }

    function loadLastProducts() {
        try {
            const savedProducts = localStorage.getItem('aiLastProducts');
            if (savedProducts) {
                const products = JSON.parse(savedProducts);
                displayProducts(products);
            }
        } catch (e) {
            console.log('Failed to load products:', e);
        }
    }

    function restoreChatMessages() {
        chatMessages.innerHTML = '';
        
        if (chatHistory.length === 0) {
            const welcomeDiv = document.createElement('div');
            welcomeDiv.className = 'chat-message ai-message';
            welcomeDiv.innerHTML = `
                <div class="message-content">
                    <strong>AI:</strong> Xin chào! Tôi có thể giúp bạn tư vấn mỹ phẩm phù hợp. Hãy mô tả loại da, nhu cầu và ngân sách của bạn nhé!
                </div>
            `;
            chatMessages.appendChild(welcomeDiv);
            return;
        }
        
        chatHistory.forEach(historyItem => {
            if (historyItem.role === 'user') {
                addMessage('user', historyItem.content, false, false);
            } else if (historyItem.role === 'assistant') {
                try {
                    const advisorData = JSON.parse(historyItem.content);
                    if (advisorData.message) {
                        addMessage('ai', advisorData.message, false, false);
                        if (advisorData.follow_up_questions?.length) {
                            addMessage('ai', 'Câu hỏi thêm: ' + advisorData.follow_up_questions.join(' | '), false, false);
                        }
                    }
                } catch (e) {
                    addMessage('ai', historyItem.content, false, false);
                }
            }
        });
    }

    if (localStorage.getItem('chatPopupOpen') === 'true') {
        chatWindow.classList.add('active');
    }

    restoreChatMessages();

    loadLastProducts();

    function addMessage(role, content, isHtml = false, saveToHistory = true) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${role === 'user' ? 'user-message' : 'ai-message'}`;
        
        const contentDiv = document.createElement('div');
        contentDiv.className = 'message-content';
        
        if (isHtml) {
            contentDiv.innerHTML = content;
        } else {
            contentDiv.innerHTML = `<strong>${role === 'user' ? 'Bạn' : 'AI'}:</strong> ${content}`;
        }
        
        messageDiv.appendChild(contentDiv);
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        return messageDiv;
    }


    function showTyping() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'typing-indicator';
        typingDiv.innerHTML = '<span></span><span></span><span></span>AI đang soạn tin...';
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return typingDiv;
    }

    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = chatInput.value.trim();
        if (!message) return;


        addMessage('user', message);
        chatHistory.push({ role: 'user', content: message });
        saveChatHistory();
        chatInput.value = '';


        const typingIndicator = showTyping();

        try {
            const response = await fetch("{{ route('chat.message') }}", {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    message: message, 
                    history: chatHistory 
                })
            });


            typingIndicator.remove();

            if (!response.ok) {
                const errorText = await response.text();
                addMessage('ai', `Lỗi ${response.status}: ${errorText.slice(0, 200)}...`);
                return;
            }

            const data = await response.json();
            

            if (data.advisor?.message) {
                addMessage('ai', data.advisor.message);
                

                if (data.advisor.follow_up_questions?.length) {
                    addMessage('ai', 'Câu hỏi thêm: ' + data.advisor.follow_up_questions.join(' | '));
                }
            }


            if (data.products?.length) {

                const productsPanel = document.querySelector('.products-panel');
                productsPanel.style.opacity = '0.5';
                
                setTimeout(() => {
                    displayProducts(data.products);
                    saveLastProducts(data.products);
                    productsPanel.style.opacity = '1';
                }, 300);
            } else {
                displayProducts([]);
            }


            chatHistory.push({ 
                role: 'assistant', 
                content: JSON.stringify(data.advisor) 
            });
            saveChatHistory();

        } catch (error) {
            typingIndicator.remove();
            addMessage('ai', 'Kết nối thất bại: ' + (error?.message || error));
        }
    });


    function displayProducts(products) {
        if (products.length === 0) {
            chatResults.innerHTML = `
                <div class="no-products">
                    <i class="bi bi-search"></i>
                    <p>Không tìm thấy sản phẩm phù hợp. Hãy thử mô tả chi tiết hơn về nhu cầu của bạn.</p>
                </div>
            `;
            return;
        }
        
        chatResults.innerHTML = '';
        
        products.forEach((product, index) => {
            const productCard = document.createElement('div');
            productCard.className = 'product-card';
            productCard.style.animationDelay = `${index * 0.1}s`;
            productCard.innerHTML = `
                <img src="/${product.image_url || ''}" alt="${product.name || ''}" 
                     loading="lazy"
                     onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%2260%22><rect width=%22100%%22 height=%22100%%22 fill=%22%23f8f9fa%22/><text x=%2250%%22 y=%2250%%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22 font-size=%2212%22>No Image</text></svg>'">
                <div class="product-name">${product.name || ''}</div>
                <div class="product-brand">${product.brand || ''} • ${product.origin || ''}</div>
                <div class="product-price">${Number(product.price || 0).toLocaleString('vi-VN')} đ</div>
                <div class="product-features">${product.features || ''}</div>
                <a href="/products/${product.id}" class="product-link" 
                   onclick="gtag && gtag('event', 'click', { event_category: 'AI Chat', event_label: 'Product View' });">
                    Xem chi tiết <i class="bi bi-arrow-right"></i>
                </a>
            `;
            chatResults.appendChild(productCard);
        });
        

        chatResults.scrollTop = 0;
    }
});
</script>