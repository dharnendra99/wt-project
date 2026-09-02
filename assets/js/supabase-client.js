/**
 * AutoPulse - Supabase Client & REST Service
 * Separate online cloud backend module for AutoPulse (100% Free Tier).
 * 
 * Instructions:
 * 1. Paste your Supabase Project URL and Anon Key below.
 * 2. If configured, AutoPulse will read/write directly to Supabase cloud!
 * 3. The PHP backend remains 100% functional locally without any changes.
 */

window.SUPABASE_CONFIG = {
    // Replace with your Supabase Project URL (e.g. 'https://xyzabcdef.supabase.co')
    url: '',
    
    // Replace with your Supabase Anon / Public Key (starts with 'eyJhbGciOi...')
    anonKey: '',

    // Optional Supabase Storage Bucket for Car Images (leave empty to use repo relative paths)
    storageBucketUrl: ''
};

var SupaDB = {
    isConfigured: function() {
        return window.SUPABASE_CONFIG.url && 
               window.SUPABASE_CONFIG.url.indexOf('supabase.co') > -1 &&
               window.SUPABASE_CONFIG.anonKey && 
               window.SUPABASE_CONFIG.anonKey.length > 20;
    },

    getHeaders: function() {
        return {
            'apikey': window.SUPABASE_CONFIG.anonKey,
            'Authorization': 'Bearer ' + window.SUPABASE_CONFIG.anonKey,
            'Content-Type': 'application/json',
            'Prefer': 'return=representation'
        };
    },

    // 1. Fetch all cars from Supabase
    getCars: function() {
        if (!this.isConfigured()) return Promise.reject('Supabase not configured');
        var url = window.SUPABASE_CONFIG.url + '/rest/v1/cars?select=*&order=price_min.asc';
        return fetch(url, { headers: this.getHeaders() })
            .then(function(res) {
                if (!res.ok) throw new Error('Supabase HTTP ' + res.status);
                return res.json();
            });
    },

    // 2. Fetch news from Supabase
    getNews: function() {
        if (!this.isConfigured()) return Promise.reject('Supabase not configured');
        var url = window.SUPABASE_CONFIG.url + '/rest/v1/news_articles?select=*&order=published_at.desc';
        return fetch(url, { headers: this.getHeaders() })
            .then(function(res) {
                if (!res.ok) throw new Error('Supabase HTTP ' + res.status);
                return res.json();
            });
    },

    // 3. Fetch approved reviews from Supabase
    getReviews: function(carId) {
        if (!this.isConfigured()) return Promise.reject('Supabase not configured');
        var url = window.SUPABASE_CONFIG.url + '/rest/v1/reviews?select=*&status=eq.approved&order=created_at.desc';
        if (carId) url += '&car_id=eq.' + carId;
        return fetch(url, { headers: this.getHeaders() })
            .then(function(res) {
                if (!res.ok) throw new Error('Supabase HTTP ' + res.status);
                return res.json();
            });
    },

    // 4. Submit a new user review to Supabase
    submitReview: function(reviewData) {
        if (!this.isConfigured()) return Promise.reject('Supabase not configured');
        var url = window.SUPABASE_CONFIG.url + '/rest/v1/reviews';
        return fetch(url, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(reviewData)
        }).then(function(res) {
            if (!res.ok) throw new Error('Supabase HTTP ' + res.status);
            return res.json();
        });
    },

    // 5. Submit article comment to Supabase
    submitComment: function(commentData) {
        if (!this.isConfigured()) return Promise.reject('Supabase not configured');
        var url = window.SUPABASE_CONFIG.url + '/rest/v1/comments';
        return fetch(url, {
            method: 'POST',
            headers: this.getHeaders(),
            body: JSON.stringify(commentData)
        }).then(function(res) {
            if (!res.ok) throw new Error('Supabase HTTP ' + res.status);
            return res.json();
        });
    }
};
