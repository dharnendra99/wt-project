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
    url: 'https://qybcwmdqrswrotgkdkda.supabase.co',
    
    // Replace with your Supabase Anon / Public Key (starts with 'eyJhbGciOi...')
    anonKey: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InF5YmN3bWRxcnN3cm90Z2tka2RhIiwicm9sZSI6ImFub24iLCJpYXQiOjE3ODgzNzI5NTcsImV4cCI6MjEwMzk0ODk1N30.wis0V1PWG0WSr5VhvFnxZn9gOmYnFbbG2tM7a--cQvM',

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

var SupaAuth = {
    client: null,
    
    init: function() {
        if (window.supabase && window.supabase.createClient && SupaDB.isConfigured()) {
            try {
                this.client = window.supabase.createClient(
                    window.SUPABASE_CONFIG.url,
                    window.SUPABASE_CONFIG.anonKey
                );
                window.supabaseClient = this.client;
            } catch (e) {
                console.warn('Supabase client init error:', e);
            }
        }
    },

    getClient: function() {
        if (!this.client) this.init();
        return this.client;
    },

    // Sign in with Google OAuth
    signInWithGoogle: function() {
        var client = this.getClient();
        if (client && client.auth) {
            return client.auth.signInWithOAuth({
                provider: 'google',
                options: {
                    redirectTo: window.location.origin + window.location.pathname
                }
            });
        }
        return Promise.reject(new Error('Supabase Auth SDK not loaded'));
    },

    // Sign in with Email & Password
    signInWithPassword: function(email, password) {
        var client = this.getClient();
        if (client && client.auth) {
            return client.auth.signInWithPassword({
                email: email,
                password: password
            }).then(function(res) {
                if (res.error) throw res.error;
                return res.data;
            });
        }
        return Promise.reject(new Error('Supabase Auth SDK not loaded'));
    },

    // Sign up with Email, Password & Name
    signUpWithPassword: function(email, password, fullName) {
        var client = this.getClient();
        if (client && client.auth) {
            return client.auth.signUp({
                email: email,
                password: password,
                options: {
                    data: {
                        full_name: fullName || '',
                        name: fullName || ''
                    }
                }
            }).then(function(res) {
                if (res.error) throw res.error;
                return res.data;
            });
        }
        return Promise.reject(new Error('Supabase Auth SDK not loaded'));
    },

    // Sign out
    signOut: function() {
        localStorage.removeItem('autopulse_demo_user');
        var client = this.getClient();
        if (client && client.auth) {
            return client.auth.signOut();
        }
        return Promise.resolve();
    },

    // Get current active user (Supabase session or demo user)
    getCurrentUser: function() {
        // Check demo local session first
        var demo = localStorage.getItem('autopulse_demo_user');
        if (demo) {
            try { return Promise.resolve(JSON.parse(demo)); } catch(e) {}
        }

        var client = this.getClient();
        if (client && client.auth) {
            return client.auth.getUser().then(function(res) {
                if (res.data && res.data.user) {
                    var u = res.data.user;
                    var meta = u.user_metadata || {};
                    return {
                        id: u.id,
                        email: u.email,
                        name: meta.full_name || meta.name || u.email.split('@')[0],
                        avatar: meta.avatar_url || meta.picture || '',
                        provider: u.app_metadata?.provider || 'email'
                    };
                }
                return null;
            }).catch(function() {
                return null;
            });
        }
        return Promise.resolve(null);
    },

    // Quick demo login for instant testing
    loginAsDemo: function(name, email) {
        var user = {
            id: 'demo_' + Date.now(),
            email: email || 'user@autopulse.in',
            name: name || 'AutoPulse Enthusiast',
            avatar: '',
            provider: 'demo'
        };
        localStorage.setItem('autopulse_demo_user', JSON.stringify(user));
        return Promise.resolve(user);
    },

    // Listen for auth changes
    onAuthStateChange: function(callback) {
        var client = this.getClient();
        if (client && client.auth) {
            return client.auth.onAuthStateChange(function(event, session) {
                if (session && session.user) {
                    var u = session.user;
                    var meta = u.user_metadata || {};
                    callback({
                        id: u.id,
                        email: u.email,
                        name: meta.full_name || meta.name || u.email.split('@')[0],
                        avatar: meta.avatar_url || meta.picture || '',
                        provider: u.app_metadata?.provider || 'email'
                    });
                } else if (!localStorage.getItem('autopulse_demo_user')) {
                    callback(null);
                }
            });
        }
    }
};

// Initialize client if SDK is already loaded
if (typeof window !== 'undefined') {
    window.SupaAuth = SupaAuth;
    window.SupaDB = SupaDB;
    if (window.supabase) {
        SupaAuth.init();
    }
}
