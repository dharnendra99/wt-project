# AutoPulse - 100% Free Supabase Cloud Backend Setup Guide

This guide walks you through setting up your free cloud database and storage on **Supabase** so that your AutoPulse website can read and write data live from the cloud without paying anything.

---

## 🌟 What We Have Prepared For You

1. **`supabase_schema.sql`**: A complete PostgreSQL script that creates all tables (`cars`, `brands`, `categories`, `news_articles`, `reviews`, `comments`, `chatbot_responses`), enables Row Level Security (RLS), sets public read/write permissions, and seeds all 6 cars with real image paths and verified specs.
2. **`assets/js/supabase-client.js`**: A clean, isolated client configuration where you only need to paste your Supabase Project URL and Anon Key.
3. **Dual Architecture (PHP + Supabase)**:
   - **Local PHP / MySQL**: Still works 100% via XAMPP with no breaking changes!
   - **Online Supabase**: Automatically takes over when configured in `supabase-client.js`.

---

## 🚀 Step-by-Step Setup (Takes ~3 Minutes)

### Step 1: Create a Free Supabase Account
1. Open [https://supabase.com](https://supabase.com) and click **"Start your project"**.
2. Sign in using your **GitHub account** (`dharnendra99`).
3. Click **"New Project"**.
4. Fill in:
   - **Name**: `autopulse`
   - **Database Password**: Choose any secure password and save it.
   - **Region**: Choose the closest region (e.g. `South Asia (Mumbai)` or `Singapore`).
   - **Pricing Plan**: Select **Free** ($0/month, no credit card required).
5. Click **"Create new project"** (takes ~60 seconds to provision).

---

### Step 2: Run the SQL Schema & Seed Data
1. In your Supabase project dashboard, click on **"SQL Editor"** (icon looking like `>_` on the left sidebar).
2. Click **"New Query"**.
3. Open the file [`supabase_schema.sql`](supabase_schema.sql) in this repository, copy its entire contents, and paste it into the query editor.
4. Click the green **"Run"** button.
5. You will see: `Success. No rows returned.`
6. Go to **"Table Editor"** on the left sidebar: you will now see all your tables with all 6 cars, news articles, and reviews ready!

---

### Step 3: Copy Your Project URL & Anon Key
1. In Supabase, click **Project Settings** (gear icon at the bottom-left).
2. Navigate to **"Data API"** (or **"API"**).
3. Find:
   - **Project URL** (looks like `https://abcdefghijkl.supabase.co`)
   - **Project API Keys &rarr; `anon` / `public` key** (starts with `eyJhbGciOi...`)

---

### Step 4: Paste Keys into `assets/js/supabase-client.js`
Open [`assets/js/supabase-client.js`](assets/js/supabase-client.js) in this project and replace lines 12–15:

```javascript
window.SUPABASE_CONFIG = {
    // Paste your Supabase Project URL here
    url: 'https://your-project-ref.supabase.co',
    
    // Paste your anon / public key here
    anonKey: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...',

    storageBucketUrl: ''
};
```

---

### Step 5: How Car Images Work (100% Free)
- **Option A (Recommended & Easiest)**:  
  Leave `storageBucketUrl: ''`. The images are already stored in your GitHub repository under `assets/images/cars/*-real.png` and served directly through Vercel's ultra-fast global CDN for free!
- **Option B (Supabase Storage Bucket)**:  
  If you want users to upload new car photos directly into Supabase:
  1. Go to **Storage** on the Supabase sidebar &rarr; click **"New bucket"**.
  2. Name it: `car-images` and toggle **Public bucket: ON**.
  3. Upload your car PNGs.
  4. Paste the public bucket URL into `storageBucketUrl` in `supabase-client.js`.

---

### Step 6: Push to GitHub & Deploy to Vercel
Run these two commands in PowerShell:

```powershell
git add .
git commit -m "Configure Supabase cloud backend"
git push origin main
```

Vercel will immediately build and deploy your live site with full Supabase cloud integration!
