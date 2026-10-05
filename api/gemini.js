/**
 * AutoPulse - Vercel Serverless Function for Gemini Chatbot
 */

module.exports = async function handler(req, res) {
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  if (req.method === 'OPTIONS') return res.status(200).end();
  if (req.method !== 'POST') return res.status(405).json({ error: 'Method Not Allowed' });

  let body = req.body;
  if (typeof body === 'string') { try { body = JSON.parse(body); } catch(e) {} }
  const { message } = body || {};
  if (!message || !message.trim()) return res.status(400).json({ reply: 'Please enter a valid message!' });

  const apiKey = process.env.GEMINI_API_KEY;
  if (!apiKey) {
    return res.status(200).json({
      reply: '⚠️ <strong>Gemini API Key missing.</strong>',
      source: 'offline'
    });
  }

  const systemPrompt = `You are the AutoPulse AI Assistant — India's premier automotive expert chatbot.
Answer car questions accurately and concisely. Use HTML: <strong>bold</strong> for names/figures, <br> for line breaks, • for bullet points. Keep under 180 words.
Featured cars: Tata Nexon (Rs 8-15.8L), XUV700 (Rs 14-27L), Creta (Rs 11-20L), Swift (Rs 6.5-9.6L), Curvv EV (Rs 17.5-22L), Punch (Rs 6-10L), Thar (Rs 11-17.6L), Verna (Rs 11-17.4L), Dzire (Rs 6.8-10L), Brezza (Rs 8.3-14L).`;

  // Step 1: Discover available models from API
  var availableModels = [];
  try {
    var listUrl = 'https://generativelanguage.googleapis.com/v1beta/models?key=' + apiKey;
    var listRes = await fetch(listUrl);
    if (listRes.ok) {
      var listData = await listRes.json();
      if (listData.models) {
        availableModels = listData.models
          .filter(function(m) {
            return m.supportedGenerationMethods && m.supportedGenerationMethods.indexOf('generateContent') > -1;
          })
          .map(function(m) { return m.name.replace('models/', ''); });
      }
    }
  } catch(e) {}

  // Step 2: Pick the best model — prefer flash-lite/flash variants
  var preferredOrder = ['gemini-2.0-flash-lite', 'gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-pro'];
  var modelsToTry = [];

  // Add preferred models that are actually available
  for (var i = 0; i < preferredOrder.length; i++) {
    if (availableModels.indexOf(preferredOrder[i]) > -1) {
      modelsToTry.push(preferredOrder[i]);
    }
  }

  // If none of the preferred matched, add any flash model available
  if (modelsToTry.length === 0) {
    for (var j = 0; j < availableModels.length; j++) {
      if (availableModels[j].indexOf('flash') > -1 || availableModels[j].indexOf('pro') > -1) {
        modelsToTry.push(availableModels[j]);
        if (modelsToTry.length >= 3) break;
      }
    }
  }

  // Final fallback: try these hardcoded names
  if (modelsToTry.length === 0) {
    modelsToTry = ['gemini-2.0-flash-lite', 'gemini-2.0-flash', 'gemini-pro'];
  }

  // Step 3: Try each model until one works
  var reply = '';
  var lastError = '';

  for (var k = 0; k < modelsToTry.length; k++) {
    var model = modelsToTry[k];
    try {
      var url = 'https://generativelanguage.googleapis.com/v1beta/models/' + model + ':generateContent?key=' + apiKey;
      var response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          contents: [{ role: 'user', parts: [{ text: systemPrompt + '\n\nUser Question: ' + message }] }],
          generationConfig: { temperature: 0.7, maxOutputTokens: 400 }
        })
      });

      if (!response.ok) {
        var errText = await response.text().catch(function() { return ''; });
        lastError = model + ': HTTP ' + response.status;
        continue;
      }

      var data = await response.json();
      if (data.candidates && data.candidates[0] && data.candidates[0].content && data.candidates[0].content.parts) {
        reply = data.candidates[0].content.parts.map(function(p) { return p.text || ''; }).join('\n').trim();
        if (reply) break;
      }
    } catch (err) {
      lastError = model + ': ' + (err.message || err);
      continue;
    }
  }

  if (!reply) {
    var debugInfo = 'Tried: ' + modelsToTry.join(', ') + '. Last error: ' + lastError + '. Available: ' + availableModels.slice(0, 8).join(', ');
    return res.status(200).json({
      reply: '⚠️ <code>' + debugInfo.substring(0, 400) + '</code>',
      source: 'offline',
      suggestions: ['Price of Nexon', 'Compare cars', 'Best EV under 25L']
    });
  }

  reply = reply.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
  reply = reply.replace(/\*(.+?)\*/g, '<em>$1</em>');
  reply = reply.replace(/\n\n/g, '<br><br>');
  reply = reply.replace(/\n/g, '<br>');

  return res.status(200).json({
    reply: reply,
    source: 'gemini',
    suggestions: ['Compare cars', 'Upcoming EVs', 'Best mileage car', 'Safest car under 20L']
  });
}
