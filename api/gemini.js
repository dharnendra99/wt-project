/**
 * AutoPulse - Vercel Serverless Function for Gemini Chatbot
 * Runs automatically on Vercel when deployed!
 */

module.exports = async function handler(req, res) {
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

  let body = req.body;
  if (typeof body === 'string') {
    try { body = JSON.parse(body); } catch(e) {}
  }
  const { message } = body || {};
  if (!message || !message.trim()) {
    return res.status(400).json({ reply: 'Please enter a valid message!' });
  }

  const apiKey = process.env.GEMINI_API_KEY;
  if (!apiKey) {
    return res.status(200).json({
      reply: '⚠️ <strong>Gemini API Key missing in Vercel.</strong><br>Please add <code>GEMINI_API_KEY</code> in Vercel Settings &rarr; Environment Variables (for Production) and click Redeploy.',
      source: 'offline',
      suggestions: ['Price of Nexon', 'Compare cars', 'Best EV under 25L']
    });
  }

  const systemPrompt = `You are the AutoPulse AI Assistant — India's premier automotive expert chatbot for the AutoPulse portal (inspired by Autocar India).

Your capabilities & instructions:
1. You have comprehensive, up-to-date knowledge of the entire Indian and global automotive market: upcoming launches, facelifts, pricing, variants, engine specs, EV range, crash safety, and road test verdicts.
2. Answer questions about ANY car accurately, authoritatively, and concisely like an Autocar India automotive journalist.
3. When asked about an upcoming car or launch timeline, provide the real automotive industry launch details, expected prices, and engine options!
4. Format your output cleanly with HTML: use <strong>bold</strong> for car names and figures, use <br> for line breaks, and bullet points (•) for specs.
5. Keep responses under 180 words, punchy and easy to read on mobile.
6. Featured AutoPulse cars: Tata Nexon Facelift (Rs 8.00-15.80L), Mahindra XUV700 (Rs 13.99-26.99L), Hyundai Creta (Rs 11.00-20.15L), Maruti Suzuki Swift (Rs 6.49-9.64L), BMW 3 Series GL (Rs 60.60-62.00L), Tata Curvv EV (Rs 17.49-21.99L), Tata Punch (Rs 6.13-10.20L), Mahindra Thar (Rs 11.35-17.60L), Hyundai Verna (Rs 11.00-17.42L), Maruti Dzire (Rs 6.79-10.14L), Maruti Brezza (Rs 8.34-14.14L).`;

  // Use a single reliable model — gemini-2.0-flash-lite is the fastest
  const models = [
    'gemini-2.0-flash-lite',
    'gemini-2.0-flash',
    'gemini-1.5-flash'
  ];

  let reply = '';
  let lastError = '';

  for (const model of models) {
    try {
      const url = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;
      const controller = new AbortController();
      const timeout = setTimeout(() => controller.abort(), 12000);

      const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        signal: controller.signal,
        body: JSON.stringify({
          contents: [
            {
              role: 'user',
              parts: [{ text: systemPrompt + '\n\nUser Question: ' + message }]
            }
          ],
          generationConfig: {
            temperature: 0.7,
            maxOutputTokens: 400
          }
        })
      });

      clearTimeout(timeout);

      if (!response.ok) {
        const errText = await response.text().catch(() => '');
        lastError = `Model ${model}: HTTP ${response.status} - ${errText.substring(0, 200)}`;
        continue;
      }

      const data = await response.json();
      if (data.candidates && data.candidates[0] && data.candidates[0].content && data.candidates[0].content.parts) {
        const parts = data.candidates[0].content.parts;
        reply = parts.map(function(p) { return p.text || ''; }).join('\n').trim();
        if (reply) break;
      }
    } catch (err) {
      lastError = `Model ${model}: ${err.message || err}`;
      continue;
    }
  }

  if (!reply) {
    return res.status(200).json({
      reply: `⚠️ Gemini API error. Debug: <code>${lastError.substring(0, 300)}</code><br><br>Please verify your <strong>GEMINI_API_KEY</strong> is valid at <a href="https://aistudio.google.com/apikey" target="_blank">Google AI Studio</a>.`,
      source: 'offline',
      suggestions: ['Price of Nexon', 'Compare cars', 'Best EV under 25L']
    });
  }

  // Clean markdown formatting to clean HTML
  reply = reply.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
  reply = reply.replace(/\*(.+?)\*/g, '<em>$1</em>');
  reply = reply.replace(/\n\n/g, '<br><br>');
  reply = reply.replace(/\n/g, '<br>');

  return res.status(200).json({
    reply,
    source: 'gemini',
    suggestions: ['Compare cars', 'Upcoming EVs', 'Best mileage car', 'Safest car under 20L']
  });
}
