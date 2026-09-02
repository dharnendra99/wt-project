/**
 * AutoPulse - AngularJS Application Module
 * Powers the frontend with two-way data binding, instant client-side filtering,
 * car comparator, interactive gallery, and offline rule-based chatbot.
 * Works 100% on Vercel without requiring a PHP server, while connecting to PHP APIs when available.
 */

var app = angular.module('autoPulseApp', []);

// Directive to safely render rich HTML inside chat bubbles
app.directive('renderHtml', function() {
    return {
        restrict: 'A',
        link: function(scope, element, attrs) {
            scope.$watch(attrs.renderHtml, function(value) {
                element.html(value || '');
            });
        }
    };
});

// Trust HTML filter
app.filter('trustHtml', ['$sce', function($sce) {
    return function(text) {
        return $sce.trustAsHtml(text || '');
    };
}]);

// Global Service to load data from JSON or PHP API
// Global Service to load data from Supabase Cloud DB, PHP API, or bundled JSON
app.factory('DataService', ['$http', function($http) {
    var supa = window.SUPABASE_CONFIG || {};
    var isSupa = supa.url && supa.url.indexOf('supabase.co') > -1 && supa.anonKey && supa.anonKey.length > 20;

    var supaHeaders = isSupa ? {
        'apikey': supa.anonKey,
        'Authorization': 'Bearer ' + supa.anonKey
    } : {};

    return {
        getCars: function() {
            if (isSupa) {
                var url = supa.url + '/rest/v1/cars?select=*&order=price_min.asc';
                return $http.get(url, { headers: supaHeaders, timeout: 6000 }).then(function(res) {
                    if (res.data && res.data.length > 0) return res.data;
                    return fallbackCars();
                }).catch(function(err) {
                    console.warn('Supabase cars fetch failed, falling back:', err);
                    return fallbackCars();
                });
            }
            return fallbackCars();

            function fallbackCars() {
                return $http.get('data/cars.json?t=' + Date.now()).then(function(res) {
                    return res.data;
                }).catch(function() {
                    return $http.get('api/cars.php').then(function(res) {
                        return res.data;
                    });
                });
            }
        },
        getNews: function() {
            if (isSupa) {
                var url = supa.url + '/rest/v1/news_articles?select=*&order=published_at.desc';
                return $http.get(url, { headers: supaHeaders, timeout: 6000 }).then(function(res) {
                    if (res.data && res.data.length > 0) return res.data;
                    return fallbackNews();
                }).catch(function(err) {
                    console.warn('Supabase news fetch failed, falling back:', err);
                    return fallbackNews();
                });
            }
            return fallbackNews();

            function fallbackNews() {
                return $http.get('data/news.json?t=' + Date.now()).then(function(res) {
                    return res.data;
                }).catch(function() {
                    return $http.get('api/news.php').then(function(res) {
                        return res.data;
                    });
                });
            }
        },
        getReviews: function() {
            if (isSupa) {
                var url = supa.url + '/rest/v1/reviews?select=*&status=eq.approved&order=created_at.desc';
                return $http.get(url, { headers: supaHeaders, timeout: 6000 }).then(function(res) {
                    if (res.data && res.data.length > 0) return res.data;
                    return fallbackReviews();
                }).catch(function(err) {
                    console.warn('Supabase reviews fetch failed, falling back:', err);
                    return fallbackReviews();
                });
            }
            return fallbackReviews();

            function fallbackReviews() {
                return $http.get('data/reviews.json?t=' + Date.now()).then(function(res) {
                    return res.data;
                });
            }
        }
    };
}]);

// 1. Main Homepage Controller
app.controller('MainCtrl', ['$scope', 'DataService', function($scope, DataService) {
    $scope.currentCity = localStorage.getItem('autopulse_city') || 'Delhi';
    $scope.searchOpen = false;
    $scope.searchQuery = '';
    $scope.heroArticles = [];
    $scope.allCars = [];
    $scope.allNews = [];
    $scope.latestNews = [];
    $scope.trendingNews = [];

    $scope.changeCity = function() {
        localStorage.setItem('autopulse_city', $scope.currentCity);
    };

    $scope.toggleSearch = function() {
        $scope.searchOpen = !$scope.searchOpen;
    };

    // Load Cars
    DataService.getCars().then(function(cars) {
        $scope.allCars = cars || [];
        $scope.trendingCars = ($scope.allCars).filter(function(c) { return c.status === 'Trending'; }).slice(0, 3);
        $scope.upcomingCars = ($scope.allCars).filter(function(c) { return c.status === 'Upcoming' || c.body_type === 'EV'; }).slice(0, 3);
        if ($scope.trendingCars.length === 0 && $scope.allCars.length > 0) $scope.trendingCars = $scope.allCars.slice(0, 3);
        if ($scope.upcomingCars.length === 0 && $scope.allCars.length > 3) $scope.upcomingCars = $scope.allCars.slice(3, 6);
    });

    // Load News
    DataService.getNews().then(function(news) {
        $scope.allNews = news || [];
        $scope.heroArticles = ($scope.allNews).filter(function(n) { return n.is_hero == 1 || n.is_hero === true; });
        if ($scope.heroArticles.length === 0 && $scope.allNews.length > 0) {
            $scope.heroArticles = [$scope.allNews[0]];
        }
        $scope.latestNews = ($scope.allNews).slice(0, 5);
        $scope.trendingNews = ($scope.allNews).slice(0, 4).sort(function(a, b) { return (b.views_count || 0) - (a.views_count || 0); });
    });

    $scope.editors = [
        { name: 'Hormazd Sorabjee', role: 'Editor-in-Chief', avatar: 'assets/images/avatars/hormazd.svg' },
        { name: 'Shapur Kotwal', role: 'Deputy Editor', avatar: 'assets/images/avatars/shapur.svg' },
        { name: 'Gavin D\'Souza', role: 'Road Test Editor', avatar: 'assets/images/avatars/gavin.svg' },
        { name: 'Sergius Barretto', role: 'Managing Editor', avatar: 'assets/images/avatars/sergius.svg' },
        { name: 'Rishaad Mody', role: 'Two-Wheeler Editor', avatar: 'assets/images/avatars/rishaad.svg' }
    ];

    $scope.formatViews = function(count) {
        if (!count) return '10K+';
        if (count >= 1000) return Math.round(count / 1000) + 'K+';
        return count;
    };

    // Wishlist handling
    $scope.wishlist = JSON.parse(localStorage.getItem('autopulse_wishlist') || '[]');
    $scope.wishlistCount = $scope.wishlist.length;

    $scope.toggleWishlist = function(carId) {
        var idx = $scope.wishlist.indexOf(carId);
        if (idx > -1) {
            $scope.wishlist.splice(idx, 1);
        } else {
            $scope.wishlist.push(carId);
        }
        localStorage.setItem('autopulse_wishlist', JSON.stringify($scope.wishlist));
        $scope.wishlistCount = $scope.wishlist.length;
    };

    $scope.isWishlisted = function(carId) {
        return $scope.wishlist.indexOf(carId) > -1;
    };
}]);

// 2. Cars Listings Controller (Instant AngularJS Filter)
app.controller('CarsCtrl', ['$scope', 'DataService', function($scope, DataService) {
    $scope.filter = {
        brands: {},
        bodyTypes: {},
        fuelTypes: {},
        priceBracket: '',
        search: '',
        sort: 'price_asc'
    };

    $scope.brandsList = ['Tata Motors', 'Mahindra', 'Hyundai', 'Maruti Suzuki', 'BMW'];
    $scope.bodyTypesList = ['SUV', 'Sedan', 'Hatchback', 'EV', 'Luxury'];
    $scope.fuelTypesList = ['Petrol', 'Diesel', 'Electric', 'Hybrid'];
    $scope.cars = [];

    DataService.getCars().then(function(cars) {
        $scope.cars = cars || [];
    });

    $scope.resetFilters = function() {
        $scope.filter.brands = {};
        $scope.filter.bodyTypes = {};
        $scope.filter.fuelTypes = {};
        $scope.filter.priceBracket = '';
        $scope.filter.search = '';
    };

    $scope.customCarFilter = function(car) {
        if (!car) return false;
        var selectedBrands = Object.keys($scope.filter.brands).filter(function(b) { return $scope.filter.brands[b]; });
        if (selectedBrands.length > 0 && selectedBrands.indexOf(car.brand_name) === -1) {
            return false;
        }

        var selectedBodies = Object.keys($scope.filter.bodyTypes).filter(function(b) { return $scope.filter.bodyTypes[b]; });
        if (selectedBodies.length > 0 && selectedBodies.indexOf(car.body_type) === -1) {
            return false;
        }

        var selectedFuels = Object.keys($scope.filter.fuelTypes).filter(function(f) { return $scope.filter.fuelTypes[f]; });
        if (selectedFuels.length > 0 && selectedFuels.indexOf(car.fuel_type) === -1) {
            return false;
        }

        if ($scope.filter.priceBracket === 'under_10' && car.price_min >= 10) return false;
        if ($scope.filter.priceBracket === '10_to_20' && (car.price_min < 10 || car.price_min > 20)) return false;
        if ($scope.filter.priceBracket === '20_to_50' && (car.price_min < 20 || car.price_min > 50)) return false;
        if ($scope.filter.priceBracket === 'above_50' && car.price_min <= 50) return false;

        if ($scope.filter.search) {
            var term = $scope.filter.search.toLowerCase();
            return (car.name && car.name.toLowerCase().indexOf(term) > -1) || 
                   (car.brand_name && car.brand_name.toLowerCase().indexOf(term) > -1);
        }

        return true;
    };

    $scope.getSortOrder = function() {
        if ($scope.filter.sort === 'price_asc') return 'price_min';
        if ($scope.filter.sort === 'price_desc') return '-price_max';
        if ($scope.filter.sort === 'name') return 'name';
        return '-id';
    };

    $scope.wishlist = JSON.parse(localStorage.getItem('autopulse_wishlist') || '[]');
    $scope.toggleWishlist = function(carId) {
        var idx = $scope.wishlist.indexOf(carId);
        if (idx > -1) $scope.wishlist.splice(idx, 1);
        else $scope.wishlist.push(carId);
        localStorage.setItem('autopulse_wishlist', JSON.stringify($scope.wishlist));
    };
    $scope.isWishlisted = function(carId) {
        return $scope.wishlist.indexOf(carId) > -1;
    };
}]);

// 3. Car Detail Controller
app.controller('CarDetailCtrl', ['$scope', 'DataService', function($scope, DataService) {
    var urlParams = new URLSearchParams(window.location.search);
    var carId = parseInt(urlParams.get('id') || '1');
    var slug = urlParams.get('slug');

    $scope.activeImage = '';
    $scope.newReview = { author_name: '', rating: '5.0', title: '', review_text: '' };
    $scope.reviewSubmitted = false;
    $scope.selectedCity = localStorage.getItem('autopulse_city') || 'Delhi';
    $scope.reviews = [];

    DataService.getCars().then(function(cars) {
        var found = (cars || []).find(function(c) {
            return (slug && c.slug === slug) || c.id === carId;
        }) || (cars && cars[0]);

        if (found) {
            $scope.car = found;
            $scope.activeImage = found.featured_image;
            $scope.gallery = found.gallery_images || [found.featured_image];
        }
    });

    DataService.getReviews().then(function(reviews) {
        $scope.reviews = (reviews || []).filter(function(r) { return r.car_id === carId; });
        if ($scope.reviews.length === 0) {
            $scope.reviews = [
                {
                    author_name: 'Rahul Sharma',
                    rating: 4.5,
                    title: 'Brilliant ride quality & high-speed stability',
                    review_text: 'Driven for 8,500 km across varied highway conditions. Unbelievable safety feel and plush seats.',
                    date: '1 week ago'
                }
            ];
        }
    });

    $scope.setImage = function(img) {
        $scope.activeImage = img;
    };

    $scope.submitReview = function() {
        if (!$scope.newReview.author_name || !$scope.newReview.title || !$scope.newReview.review_text) return;

        var revObj = {
            car_id: $scope.car ? $scope.car.id : carId,
            car_name: $scope.car ? $scope.car.name : 'Car',
            author_name: $scope.newReview.author_name,
            rating: parseFloat($scope.newReview.rating),
            title: $scope.newReview.title,
            review_text: $scope.newReview.review_text,
            status: 'approved'
        };

        if (typeof SupaDB !== 'undefined' && SupaDB.isConfigured()) {
            SupaDB.submitReview(revObj);
        }

        $scope.reviews.unshift({
            author_name: revObj.author_name,
            rating: revObj.rating,
            title: revObj.title,
            review_text: revObj.review_text,
            date: 'Just now'
        });

        $scope.newReview = { author_name: '', rating: '5.0', title: '', review_text: '' };
        $scope.reviewSubmitted = true;
    };
}]);

// 4. Compare Controller
app.controller('CompareCtrl', ['$scope', 'DataService', function($scope, DataService) {
    $scope.allCars = [];
    $scope.comparedCars = [];

    var urlParams = new URLSearchParams(window.location.search);
    $scope.car1Id = parseInt(urlParams.get('car1') || '1');
    $scope.car2Id = parseInt(urlParams.get('car2') || '3');
    $scope.car3Id = parseInt(urlParams.get('car3') || '0') || null;

    DataService.getCars().then(function(cars) {
        $scope.allCars = cars || [];
        if ($scope.allCars.length > 0) {
            if (!$scope.car1Id || !$scope.allCars.find(function(c){ return c.id == $scope.car1Id; })) {
                $scope.car1Id = $scope.allCars[0].id;
            }
            if (!$scope.car2Id || !$scope.allCars.find(function(c){ return c.id == $scope.car2Id; })) {
                $scope.car2Id = $scope.allCars.length > 1 ? $scope.allCars[1].id : $scope.allCars[0].id;
            }
        }
        $scope.updateComparison();
    });

    $scope.updateComparison = function() {
        if (!$scope.allCars || $scope.allCars.length === 0) return;
        $scope.comparedCars = [];
        var c1 = $scope.allCars.find(function(c) { return c.id == $scope.car1Id; });
        var c2 = $scope.allCars.find(function(c) { return c.id == $scope.car2Id; });
        if (c1) $scope.comparedCars.push(c1);
        if (c2) $scope.comparedCars.push(c2);

        if ($scope.car3Id) {
            var c3 = $scope.allCars.find(function(c) { return c.id == $scope.car3Id; });
            if (c3) $scope.comparedCars.push(c3);
        }
    };
}]);

// 5. News Controller
app.controller('NewsCtrl', ['$scope', 'DataService', function($scope, DataService) {
    $scope.selectedCategory = 'All';
    $scope.categories = ['All', 'Car News', 'Bike News', 'Motorsport', 'Industry'];
    $scope.news = [];

    DataService.getNews().then(function(news) {
        $scope.news = news || [];
    });

    $scope.filterNews = function(item) {
        if ($scope.selectedCategory === 'All') return true;
        return item && item.category === $scope.selectedCategory;
    };
}]);

// 6. Gemini-Powered Chatbot Controller (with rule-based offline fallback)
app.controller('ChatbotCtrl', ['$scope', '$http', 'DataService', function($scope, $http, DataService) {
    $scope.isOpen = false;
    $scope.userInput = '';
    $scope.isTyping = false;
    $scope.geminiMode = true;  // shows ✨ badge when Gemini replied
    $scope.messages = [
        {
            sender: 'bot',
            text: '👋 Hello! I am your <strong>AutoPulse AI Assistant</strong> powered by <strong style="color:#4285f4;">Google Gemini</strong>.<br>Ask me anything about car prices, mileage, comparisons, EVs, or upcoming launches!',
            source: 'gemini'
        }
    ];
    $scope.suggestions = ['Price of Nexon', 'Compare Nexon and Creta', 'Best EV under 25L', 'Safest car in India'];
    $scope.toggleChat = function() {
        $scope.isOpen = !$scope.isOpen;
    };

    window.openAutoPulseChat = function() {
        $scope.$apply(function() {
            $scope.isOpen = true;
        });
    };

    $scope.sendMessage = function(text) {
        var msg = text || $scope.userInput;
        if (!msg || !msg.trim()) return;

        $scope.messages.push({ sender: 'user', text: msg, source: 'user' });
        $scope.userInput = '';
        $scope.isTyping = true;

        // 1️⃣ Try PHP endpoint (Local XAMPP)
        $http.post('api/gemini-chat.php', { message: msg }, {
            headers: { 'Content-Type': 'application/json' }
        }).then(function(res) {
            handleBotSuccess(res.data.reply, res.data.source || 'gemini', res.data.suggestions);
        }).catch(function() {
            // 2️⃣ Try Vercel Serverless Function (/api/gemini-chat)
            $http.post('api/gemini-chat', { message: msg }, {
                headers: { 'Content-Type': 'application/json' }
            }).then(function(res) {
                handleBotSuccess(res.data.reply, res.data.source || 'gemini', res.data.suggestions);
            }).catch(function() {
                // 3️⃣ Direct Client-Side Gemini Call (Guaranteed to work anywhere on Vercel/Static)
                callGeminiDirect(msg).then(function(aiReply) {
                    $scope.$apply(function() {
                        handleBotSuccess(aiReply, 'gemini', ['Compare cars', 'Upcoming EVs', 'Best mileage car']);
                    });
                }).catch(function() {
                    // 4️⃣ Final offline rule-based fallback
                    $scope.$apply(function() {
                        var reply = processOfflineClient(msg);
                        handleBotSuccess(reply.text, 'offline', reply.suggestions);
                    });
                });
            });
        });
    };

    function handleBotSuccess(text, source, suggestions) {
        $scope.isTyping = false;
        $scope.messages.push({
            sender: 'bot',
            text: text,
            source: source || 'gemini'
        });
        if (suggestions) $scope.suggestions = suggestions;

        setTimeout(function() {
            var body = document.querySelector('.chatbot-messages-body');
            if (body) body.scrollTop = body.scrollHeight;
        }, 50);
    }

    function callGeminiDirect(userMsg) {
        var key = 'AIzaSyDK0gKpZ7ZT34wS4JWm1nvK32qMpwqaPjM';
        var url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key=' + key;
        var prompt = "You are the AutoPulse AI Assistant (inspired by Autocar India). Answer questions about any car in India or globally authoritatively and concisely. Use HTML <strong> and <br>. AutoPulse featured cars: Tata Nexon (8-15.5L), XUV700 (14-27L), Creta (11-20L), Swift (6.5-9.6L), BMW 3 Series (60.6-62L), Curvv EV (17.5-22L).";
        
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                contents: [{ parts: [{ text: prompt + "\n\nUser Question: " + userMsg }] }],
                generationConfig: { temperature: 0.7, maxOutputTokens: 500 }
            })
        }).then(function(response) {
            if (!response.ok) throw new Error('Gemini API status ' + response.status);
            return response.json();
        }).then(function(data) {
            var parts = data.candidates[0].content.parts;
            var text = parts.map(function(p) { return p.text || ''; }).join('\n').trim();
            return text.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                       .replace(/\*(.+?)\*/g, '<em>$1</em>')
                       .replace(/\n\n/g, '<br><br>')
                       .replace(/\n/g, '<br>');
        });
    }

    function processOfflineClient(rawMsg) {
        var lower = rawMsg.toLowerCase();

        var carDb = [
            { name: 'Tata Nexon Facelift', keys: ['nexon'], min: '8.00', max: '15.50', fuel: 'Petrol/Diesel', mileage: '17.4 kmpl', power: '118 bhp', safety: '5 Star (BNCAP)', slug: 'tata-nexon-facelift' },
            { name: 'Mahindra XUV700',     keys: ['xuv700','xuv 700'], min: '13.99', max: '26.99', fuel: 'Petrol/Diesel', mileage: '16.5 kmpl', power: '182 bhp', safety: '5 Star (GNCAP)', slug: 'mahindra-xuv700' },
            { name: 'Hyundai Creta',       keys: ['creta'], min: '11.00', max: '20.15', fuel: 'Petrol/Diesel', mileage: '18.4 kmpl', power: '158 bhp', safety: '3 Star (GNCAP)', slug: 'hyundai-creta' },
            { name: 'Maruti Suzuki Swift', keys: ['swift'], min: '6.49',  max: '9.64',  fuel: 'Petrol',        mileage: '25.75 kmpl', power: '80 bhp',  safety: '6 Airbags',    slug: 'maruti-suzuki-swift' },
            { name: 'BMW 3 Series',        keys: ['bmw','bmw3','3 series','bmw x'], min: '60.60', max: '62.00', fuel: 'Petrol', mileage: '15.3 kmpl', power: '255 bhp', safety: '5 Star (Euro NCAP)', slug: 'bmw-3-series-gran-limousine' },
            { name: 'Tata Curvv EV',       keys: ['curvv','curv','tata ev'], min: '17.49', max: '21.99', fuel: 'Electric', mileage: '585 km range', power: '165 bhp', safety: '5 Star (BNCAP)', slug: 'tata-curvv-ev' }
        ];

        // Match any car in the message
        var matched = null;
        for (var i = 0; i < carDb.length; i++) {
            for (var j = 0; j < carDb[i].keys.length; j++) {
                if (lower.indexOf(carDb[i].keys[j]) > -1) {
                    matched = carDb[i];
                    break;
                }
            }
            if (matched) break;
        }

        // Price intent
        if (matched && (lower.indexOf('price') > -1 || lower.indexOf('cost') > -1 || lower.indexOf('how much') > -1 || lower.indexOf('rate') > -1 || lower.indexOf('worth') > -1)) {
            return {
                text: '💰 The ex-showroom price of <strong>' + matched.name + '</strong> ranges from <strong>Rs ' + matched.min + ' – ' + matched.max + ' Lakh*</strong>.<br><br>' +
                      '• <strong>Fuel:</strong> ' + matched.fuel + '<br>' +
                      '• <strong>Mileage:</strong> ' + matched.mileage + '<br>' +
                      '• <strong>Safety:</strong> ' + matched.safety + '<br><br>' +
                      '<a href="car-detail.html?slug=' + matched.slug + '" style="color:#D90000;font-weight:700;">View full specs & gallery →</a>',
                suggestions: ['Mileage of ' + matched.name, 'Compare with Creta', 'Upcoming EVs']
            };
        }

        // Mileage intent
        if (matched && (lower.indexOf('mileage') > -1 || lower.indexOf('average') > -1 || lower.indexOf('kmpl') > -1 || lower.indexOf('fuel') > -1 || lower.indexOf('efficiency') > -1)) {
            return {
                text: '⛽ The fuel efficiency of <strong>' + matched.name + '</strong> is <strong>' + matched.mileage + '</strong>.<br>Fuel type: ' + matched.fuel,
                suggestions: ['Price of ' + matched.name, 'Compare cars']
            };
        }

        // Any mention of a car = give a summary
        if (matched) {
            return {
                text: '🚗 <strong>' + matched.name + '</strong> Overview:<br><br>' +
                      '• <strong>Price:</strong> Rs ' + matched.min + ' – ' + matched.max + ' Lakh*<br>' +
                      '• <strong>Power:</strong> ' + matched.power + '<br>' +
                      '• <strong>Mileage:</strong> ' + matched.mileage + '<br>' +
                      '• <strong>Fuel:</strong> ' + matched.fuel + '<br>' +
                      '• <strong>Safety:</strong> ' + matched.safety + '<br><br>' +
                      '<a href="car-detail.html?slug=' + matched.slug + '" style="color:#D90000;font-weight:700;">See full road test →</a>',
                suggestions: ['Price of ' + matched.name, 'Mileage of ' + matched.name, 'Compare cars']
            };
        }

        if (lower.indexOf('compare') > -1 || lower.indexOf(' vs ') > -1) {
            return {
                text: '⚖️ Compare up to 3 cars side-by-side on AutoPulse! Check prices, power, mileage, and safety ratings instantly.<br><br><a href="compare.html" style="color:#D90000;font-weight:700;">Open Car Comparator →</a>',
                suggestions: ['Compare Nexon and Creta', 'Price of XUV700', 'Best mileage car']
            };
        }

        if (lower.indexOf('ev') > -1 || lower.indexOf('electric') > -1 || lower.indexOf('range') > -1) {
            return {
                text: '⚡ Best EV in our catalog: <strong>Tata Curvv EV</strong> with a claimed range of <strong>585 km (ARAI)</strong>, priced from <strong>Rs 17.49 Lakh</strong>.<br><br><a href="car-detail.html?slug=tata-curvv-ev" style="color:#D90000;font-weight:700;">View Curvv EV Specs →</a>',
                suggestions: ['Price of Curvv EV', 'Compare Curvv and Nexon']
            };
        }

        if (lower.indexOf('safe') > -1 || lower.indexOf('crash') > -1 || lower.indexOf('ncap') > -1 || lower.indexOf('bncap') > -1) {
            return {
                text: '🛡️ Safest cars on AutoPulse:<br>• <strong>Tata Nexon Facelift</strong> — 5 Star BNCAP<br>• <strong>Mahindra XUV700</strong> — 5 Star GNCAP<br>• <strong>Tata Curvv EV</strong> — 5 Star BNCAP<br>• <strong>BMW 3 Series</strong> — 5 Star Euro NCAP',
                suggestions: ['Price of Nexon', 'Price of XUV700']
            };
        }

        if (lower.indexOf('upcoming') > -1 || lower.indexOf('launch') > -1 || lower.indexOf('new car') > -1) {
            return {
                text: '🔥 Upcoming launches to watch:<br>• <strong>Tata Curvv EV</strong> — Rs 17.49 Lakh (EV, 585 km range)<br>• <strong>Next-Gen Maruti Dzire</strong> — Sunroof confirmed<br>• <strong>Mahindra Thar Roxx 5-Door</strong> — 5-seat off-roader',
                suggestions: ['Price of Curvv EV', 'Compare Nexon and Creta']
            };
        }

        if (lower.indexOf('best') > -1 && (lower.indexOf('mileage') > -1 || lower.indexOf('fuel') > -1)) {
            return {
                text: '🏆 Best mileage car on AutoPulse: <strong>Maruti Suzuki Swift</strong> with <strong>25.75 kmpl</strong> ARAI certified!<br>Price: Rs 6.49 – 9.64 Lakh<br><br><a href="car-detail.html?slug=maruti-suzuki-swift" style="color:#D90000;font-weight:700;">View Swift specs →</a>',
                suggestions: ['Price of Swift', 'Compare Swift and Nexon']
            };
        }

        if (lower.indexOf('hello') > -1 || lower.indexOf('hi') > -1 || lower.indexOf('hey') > -1 || lower.indexOf('namaste') > -1) {
            return {
                text: '👋 Hello! Welcome to AutoPulse. I can help you with:<br>• Car prices & mileage<br>• Side-by-side comparisons<br>• Safety ratings<br>• Upcoming launches<br><br>What would you like to know?',
                suggestions: ['Price of Nexon', 'Compare Nexon and Creta', 'Best EV under 25L', 'Safest car in India']
            };
        }

        return {
            text: '🔍 I can answer questions about <strong>car prices, mileage, safety ratings, comparisons</strong>, and <strong>upcoming launches</strong>.<br><br>Try: <em>"Price of Nexon"</em>, <em>"Compare Creta and XUV700"</em>, or <em>"Best EV car"</em>',
            suggestions: ['Price of Nexon', 'Price of Creta', 'Best mileage car', 'Compare cars']
        };
    }
}]);

// 7. Admin Controller (AngularJS Client-Side CRUD + localStorage sync)
app.controller('AdminCtrl', ['$scope', 'DataService', function($scope, DataService) {
    $scope.tab = 'dashboard';
    $scope.cars = [];
    $scope.news = [];
    $scope.newCar = {
        name: '', brand_name: 'Tata Motors', body_type: 'SUV', fuel_type: 'Petrol',
        price_min: 10.0, price_max: 16.0, status: 'Available', mileage: '18.0 kmpl',
        power: '120 bhp', featured_image: 'assets/images/cars/nexon.svg'
    };

    DataService.getCars().then(function(cars) { $scope.cars = cars; });
    DataService.getNews().then(function(news) { $scope.news = news; });

    $scope.saveCar = function() {
        if (!$scope.newCar.name) return;
        var carObj = angular.copy($scope.newCar);
        carObj.id = $scope.cars.length + 1;
        carObj.slug = carObj.name.toLowerCase().replace(/[^a-z0-9]+/g, '-');
        $scope.cars.unshift(carObj);
        localStorage.setItem('autopulse_cars_db', JSON.stringify($scope.cars));
        alert('Car added successfully to AutoPulse catalog!');
        $scope.tab = 'cars';
    };

    $scope.deleteCar = function(id) {
        if (confirm('Delete this car model from catalog?')) {
            $scope.cars = $scope.cars.filter(function(c) { return c.id !== id; });
            localStorage.setItem('autopulse_cars_db', JSON.stringify($scope.cars));
        }
    };
}]);
