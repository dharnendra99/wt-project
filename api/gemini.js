/**
 * AutoPulse - Vercel Serverless Function for Gemini Chatbot
 * Runs automatically on Vercel when deployed!
 */

export default async function handler(req, res) {
  // Set CORS headers
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  if (req.method !== 'POST') {
    return res.status(405).json({ error: 'Method Not Allowed' });
  }

  const { message } = req.body || {};
  if (!message || !message.trim()) {
    return res.status(400).json({ reply: 'Please enter a valid message!' });
  }

  const apiKey = process.env.GEMINI_API_KEY;

  const systemPrompt = `You are the AutoPulse AI Assistant — India's premier automotive expert chatbot for the AutoPulse portal (inspired by Autocar India).

Your capabilities & instructions:
1. You have comprehensive, up-to-date knowledge of the entire Indian and global automotive market: upcoming launches, facelifts, pricing, variants, engine specs, EV range, crash safety, and road test verdicts (e.g. BMW X5, Mahindra Thar Roxx, Maruti Dzire, Tata Curvv, Creta, Fortuner, etc.).
2. Answer questions about ANY car accurately, authoritatively, and concisely like an Autocar India automotive journalist.
3. When asked about an upcoming car or launch timeline, provide the real automotive industry launch details, expected prices, and engine options!
4. Format your output cleanly with HTML: use <strong>bold</strong> for car names and figures, use <br> for line breaks, and bullet points (•) for specs.
5. Keep responses under 180 words, punchy and easy to read on mobile.
6. Featured AutoPulse cars: Tata Nexon Facelift (Rs 8.00 - 15.50 L), Mahindra XUV700 (Rs 13.99 - 26.99 L), Hyundai Creta (Rs 11.00 - 20.15 L), Maruti Suzuki Swift (Rs 6.49 - 9.64 L), BMW 3 Series Gran Limousine (Rs 60.60 - 62.00 L), Tata Curvv EV (Rs 17.49 - 21.99 L).`;

  try {
    const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-3.6-flash:generateContent?key=${apiKey}`;
    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        contents: [
          {
            role: 'user',
            parts: [{ text: `${systemPrompt}\n\nUser Question: ${message}` }]
          }
        ],
        generationConfig: {
          temperature: 0.7,
          maxOutputTokens: 600
        }
      })
    });

    if (!response.ok) {
      throw new Error(`Gemini API returned status ${response.status}`);
    }

    const data = await response.json();
    let reply = '';

    if (data.candidates && data.candidates[0]?.content?.parts) {
      const parts = data.candidates[0].content.parts;
      reply = parts.map(p => p.text || '').join('\n').trim();
    }

    if (!reply) {
      throw new Error('No candidate reply text received');
    }

    // Clean markdown formatting if any
    reply = reply.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    reply = reply.replace(/\*(.+?)\*/g, '<em>$1</em>');
    reply = reply.replace(/\n\n/g, '<br><br>');
    reply = reply.replace(/\n/g, '<br>');

    return res.status(200).json({
      reply,
      source: 'gemini',
      suggestions: ['Compare cars', 'Upcoming EVs', 'Best mileage car', 'Safest car under 20L']
    });
  } catch (err) {
    return res.status(500).json({
      error: err.message,
      reply: `Gemini AI encountered an error: ${err.message}`,
      source: 'error'
    });
  }
}
