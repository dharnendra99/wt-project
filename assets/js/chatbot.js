/**
 * AutoPulse - Offline Rule-Based Chatbot Assistant
 * Floating widget, keyword matching, and dynamic price/spec querying.
 * Connects to chatbot.php locally, with client-side fallback for Vercel deployment.
 */

document.addEventListener('DOMContentLoaded', () => {
    const chatContainer = document.getElementById('chatbotContainer');
    const toggleBtn = document.getElementById('chatbotToggleBtn');
    const closeBtn = document.getElementById('chatbotCloseBtn');
    const sendBtn = document.getElementById('chatSendBtn');
    const inputField = document.getElementById('chatInputField');
    const messagesBody = document.getElementById('chatbotMessagesBody');

    if (!chatContainer || !toggleBtn) return;

    // Toggle Chat Window
    function toggleChat() {
        chatContainer.classList.toggle('active');
        if (chatContainer.classList.contains('active')) {
            if (inputField) inputField.focus();
        }
    }

    toggleBtn.addEventListener('click', toggleChat);
    if (closeBtn) closeBtn.addEventListener('click', toggleChat);

    // Handle quick suggestion chips
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('chat-chip')) {
            const prompt = e.target.getAttribute('data-prompt') || e.target.textContent;
            sendMessage(prompt);
        }
    });

    // Send on Enter
    if (inputField) {
        inputField.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                handleSend();
            }
        });
    }

    if (sendBtn) {
        sendBtn.addEventListener('click', handleSend);
    }

    function handleSend() {
        const text = inputField.value.trim();
        if (!text) return;
        sendMessage(text);
        inputField.value = '';
    }

    function sendMessage(messageText) {
        // Render user message bubble
        appendMessage(messageText, 'user');

        // Show typing indicator
        const typingId = showTypingIndicator();

        // Send to backend chatbot.php
        fetch('chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: messageText })
        })
        .then(res => {
            if (!res.ok) throw new Error('Chatbot backend unavailable');
            return res.json();
        })
        .then(data => {
            removeTypingIndicator(typingId);
            appendMessage(data.reply, 'bot', data.suggestions);
        })
        .catch(() => {
            // Client-side rule engine fallback for Vercel deployment!
            processOfflineClientFallback(messageText, typingId);
        });
    }

    function appendMessage(text, sender, suggestions = null) {
        const msgRow = document.createElement('div');
        msgRow.className = `chat-message ${sender}`;

        const bubble = document.createElement('div');
        bubble.className = 'chat-bubble';
        bubble.innerHTML = text.replace(/\n/g, '<br>');

        msgRow.appendChild(bubble);
        messagesBody.appendChild(msgRow);

        // Add suggestion chips if provided
        if (suggestions && suggestions.length > 0) {
            const sugWrap = document.createElement('div');
            sugWrap.className = 'chat-suggestions-row';
            suggestions.forEach(item => {
                const chip = document.createElement('span');
                chip.className = 'chat-chip';
                chip.textContent = item;
                chip.setAttribute('data-prompt', item);
                sugWrap.appendChild(chip);
            });
            messagesBody.appendChild(sugWrap);
        }

        // Scroll to bottom
        messagesBody.scrollTop = messagesBody.scrollHeight;
    }

    function showTypingIndicator() {
        const id = 'typing_' + Date.now();
        const typingRow = document.createElement('div');
        typingRow.className = 'chat-message bot';
        typingRow.id = id;
        typingRow.innerHTML = `
            <div class="chat-bubble" style="color: #888; font-style: italic;">
                AutoPulse is typing...
            </div>
        `;
        messagesBody.appendChild(typingRow);
        messagesBody.scrollTop = messagesBody.scrollHeight;
        return id;
    }

    function removeTypingIndicator(id) {
        const el = document.getElementById(id);
        if (el) el.remove();
    }

    // Client-side rule matching when PHP is not present (Vercel)
    function processOfflineClientFallback(rawText, typingId) {
        setTimeout(() => {
            removeTypingIndicator(typingId);
            const lower = rawText.toLowerCase();

            // Check dynamic car price queries
            const carDatabase = [
                { name: 'Tata Nexon', min: '8.00', max: '15.50', fuel: 'Petrol/Diesel', mileage: '17.4 kmpl' },
                { name: 'Mahindra XUV700', min: '13.99', max: '26.99', fuel: 'Petrol/Diesel', mileage: '16.5 kmpl' },
                { name: 'Hyundai Creta', min: '11.00', max: '20.15', fuel: 'Petrol/Diesel', mileage: '18.4 kmpl' },
                { name: 'Maruti Suzuki Swift', min: '6.49', max: '9.64', fuel: 'Petrol', mileage: '25.75 kmpl' },
                { name: 'BMW 3 Series', min: '60.60', max: '62.00', fuel: 'Petrol', mileage: '15.3 kmpl' },
                { name: 'Tata Curvv EV', min: '17.49', max: '21.99', fuel: 'Electric', mileage: '585 km range' }
            ];

            let matchedCar = carDatabase.find(c => lower.includes(c.name.toLowerCase()) || lower.includes(c.name.split(' ').pop().toLowerCase()));

            if (lower.includes('price') && matchedCar) {
                appendMessage(`The ex-showroom price of <strong>${matchedCar.name}</strong> is <strong>Rs ${matchedCar.min} - ${matchedCar.max} Lakh</strong>. It delivers an estimated ${matchedCar.mileage} and runs on ${matchedCar.fuel}.`, 'bot', ['Compare with Creta', 'Latest news', 'Book test drive']);
                return;
            }

            if (matchedCar && (lower.includes('mileage') || lower.includes('average') || lower.includes('kmpl'))) {
                appendMessage(`The fuel efficiency of <strong>${matchedCar.name}</strong> is <strong>${matchedCar.mileage}</strong>.`, 'bot');
                return;
            }

            if (lower.includes('compare')) {
                appendMessage('You can compare any 2 or 3 cars head-to-head on our Compare page. Choose models to view horsepower, mileage, boot space, and safety ratings side-by-side!', 'bot', ['Compare Nexon and Creta', 'Compare Swift and Nexon']);
                return;
            }

            if (lower.includes('test drive') || lower.includes('book')) {
                appendMessage('To book a test drive, visit the car detail page and select "Book Test Drive", or visit an authorized showroom nearby.', 'bot');
                return;
            }

            if (lower.includes('upcoming') || lower.includes('launch')) {
                appendMessage('Exciting upcoming cars include Tata Curvv EV (just launched!), Next-Gen Maruti Dzire with electric sunroof, and the 5-door Mahindra Thar Roxx!', 'bot', ['Tell me about Curvv EV', 'Thar Roxx review']);
                return;
            }

            if (lower.includes('hello') || lower.includes('hi') || lower.includes('hey')) {
                appendMessage('Hello! Welcome to AutoPulse Assistant. How can I help you? You can ask me for car prices, comparisons, fuel economy, or latest news.', 'bot', ['Price of Nexon', 'Price of Creta', 'Compare cars', 'Upcoming cars']);
                return;
            }

            // Fallback response
            appendMessage("I couldn't find an exact match for that. Try asking about car prices (e.g. 'Price of Nexon'), comparisons, fuel efficiency, or test drive bookings!", 'bot', ['Price of Creta', 'Compare cars', 'Best mileage car', 'Contact support']);
        }, 500);
    }
});
