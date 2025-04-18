<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Complete Digital Marketing Course | EduPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af'
                        },
                        secondary: {
                            400: '#60a5fa',
                            500: '#3b82f6'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .course-card {
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            border-color: rgba(59, 130, 246, 0.5);
        }
        
        .lesson-checkbox:checked + label {
            text-decoration: line-through;
            color: #9CA3AF;
        }
        
        .progress-bar {
            transition: width 0.5s ease;
        }
        
        .tooltip {
            opacity: 0;
            transition: opacity 0.2s ease;
            pointer-events: none;
        }
        
        .has-tooltip:hover .tooltip {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
    <nav class="bg-gray-800 border-b border-gray-700 py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="#" class="flex items-center">
                        <i class="fas fa-graduation-cap text-primary-600 text-2xl"></i>
                        <span class="ml-2 text-xl font-bold">EduPro</span>
                    </a>
                    <div class="hidden md:flex space-x-6">
                        <a href="#" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Courses</a>
                        <a href="resources.html" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Resources</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="p-2 rounded-full hover:bg-gray-700 transition relative has-tooltip">
                        <i class="fas fa-search text-gray-400"></i>
                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                            Search courses
                        </span>
                    </button>
                    <button class="p-2 rounded-full hover:bg-gray-700 transition relative has-tooltip">
                        <i class="fas fa-bell text-gray-400"></i>
                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                            Notifications
                        </span>
                    </button>
                    <div class="relative">
                        <button class="flex items-center space-x-2 hover:bg-gray-700 px-3 py-2 rounded-full transition">
                            <img src="https://randomuser.me/api/portraits/men/46.jpg" class="w-8 h-8 rounded-full">
                            <span id="completion-badge" class="hidden absolute -top-1 -right-1 bg-primary-600 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-gray-800 rounded-xl p-8 mb-8 border border-gray-700">
            <div class="flex flex-col md:flex-row justify-between">
                <div class="md:w-2/3">
                    <span class="inline-block bg-primary-600 text-white px-3 py-1 rounded-full text-xs font-semibold mb-4">
                        PROFESSIONAL CERTIFICATE
                    </span>
                    <h1 class="text-3xl md:text-4xl font-bold mb-4">The Complete Digital Marketing Course</h1>
                    <p class="text-lg text-gray-300 mb-6">Master all digital marketing channels including SEO, Social Media, PPC, Email Marketing, Analytics and more with real-world case studies.</p>
                    
                    <div class="flex flex-wrap items-center gap-4 mb-6">
                        <div class="flex items-center">
                            <div class="flex items-center text-yellow-400 mr-1">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                            <span class="text-sm">4.6 (3,850 ratings)</span>
                        </div>
                        <span class="text-gray-400">•</span>
                        <span class="text-sm">42,000+ students enrolled</span>
                        <span class="text-gray-400">•</span>
                        <span class="text-sm">Updated April 2024</span>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <button class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium transition">
                            Enroll Now
                        </button>
                        <button class="flex items-center text-gray-300 hover:text-white">
                            <i class="far fa-heart mr-2"></i> Save
                        </button>
                    </div>
                </div>
                <div class="hidden md:block md:w-1/3">
                    <div class="relative overflow-hidden rounded-lg">
                        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80" 
                             alt="Digital Marketing Course" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent flex items-end p-4">
                            <button class="flex items-center justify-center w-12 h-12 bg-white/20 backdrop-blur-sm rounded-full hover:bg-white/30 transition">
                                <i class="fas fa-play text-white"></i>
                            </button>
                            <span class="ml-3 text-white font-medium">Course Preview</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-gray-800 rounded-xl p-6 mb-8 border border-gray-700">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold flex items-center">
                    <i class="fas fa-chart-line text-primary-600 mr-3"></i>
                    Your Learning Progress
                </h2>
                <span id="progress-text" class="text-sm text-gray-300">0/20 lessons completed</span>
            </div>
            <div class="w-full bg-gray-700 rounded-full h-2.5 mb-2">
                <div id="progress-bar" class="bg-primary-600 h-2.5 rounded-full progress-bar" style="width: 0%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-400">
                <span>Beginner</span>
                <span>Intermediate</span>
                <span>Advanced</span>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="lg:col-span-2">
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-8">
                    <h2 class="text-2xl font-bold mb-6">Course Curriculum</h2>
                    
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-primary-500">Module 1: Digital Marketing Fundamentals</h3>
                            <span class="text-sm text-gray-400">4 lessons</span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-1" class="lesson-checkbox hidden">
                                    <label for="lesson-1" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Digital Marketing Landscape</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">25 min</span>
                                    <a href="https://www.youtube.com/watch?v=Odz6OelLH-k" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-2" class="lesson-checkbox hidden">
                                    <label for="lesson-2" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Customer Journey Mapping</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">30 min</span>
                                    <a href="https://www.youtube.com/watch?v=X7iXcP-wIkk" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-3" class="lesson-checkbox hidden">
                                    <label for="lesson-3" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Creating a Marketing Strategy</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">40 min</span>
                                    <a href="https://www.youtube.com/watch?v=bCoL1bKSYSo" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-purple-500">Module 2: SEO & Content Marketing</h3>
                            <span class="text-sm text-gray-400">5 lessons</span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-4" class="lesson-checkbox hidden">
                                    <label for="lesson-4" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Keyword Research & On-Page SEO</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">45 min</span>
                                    <a href="https://www.youtube.com/watch?v=IrFAeQgzE7w" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-5" class="lesson-checkbox hidden">
                                    <label for="lesson-5" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Content Strategy & Blogging</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">35 min</span>
                                    <a href="https://www.youtube.com/watch?v=MD5-HByRxoA" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-xl font-semibold text-green-500">Module 3: Social Media Marketing</h3>
                            <span class="text-sm text-gray-400">4 lessons</span>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-between bg-gray-700/50 p-4 rounded-lg border border-gray-600">
                                <div class="flex items-center">
                                    <input type="checkbox" id="lesson-6" class="lesson-checkbox hidden">
                                    <label for="lesson-6" class="flex items-center cursor-pointer">
                                        <span class="w-5 h-5 border border-gray-500 rounded mr-4 flex items-center justify-center transition">
                                            <i class="fas fa-check text-xs text-primary-600 hidden"></i>
                                        </span>
                                        <span>Facebook & Instagram Marketing</span>
                                    </label>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="text-sm text-gray-400">50 min</span>
                                    <a href="https://www.youtube.com/watch?v=MxnLHlmuwxY" class="text-primary-600 hover:text-primary-500 text-sm has-tooltip">
                                        <i class="fas fa-play"></i>
                                        <span class="tooltip absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs py-1 px-2 rounded whitespace-nowrap">
                                            Start Lesson
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-8">
                        <button class="text-primary-600 hover:text-primary-500 font-medium">
                            Show all 8 modules <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                    </div>
                </div>
                
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                    <h2 class="text-2xl font-bold mb-6">About the Instructor</h2>
                    
                    <div class="flex flex-col md:flex-row items-start">
                        <img src="https://randomuser.me/api/portraits/women/32.jpg" class="w-24 h-24 rounded-full object-cover mb-4 md:mb-0 md:mr-6 border-4 border-primary-600/30">
                        <div>
                            <h3 class="text-xl font-bold mb-1">Jessica Martinez</h3>
                            <p class="text-primary-500 mb-3">Digital Marketing Director | Former Google Ads Specialist</p>
                            <p class="text-gray-300 mb-4">
                                With 12 years of experience helping brands grow online, Jessica has managed over $20M in digital ad spend and trained thousands of marketers. 
                                She brings practical, results-driven strategies from working with Fortune 500 companies and startups alike.
                            </p>
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center text-sm text-gray-400">
                                    <i class="fas fa-star text-yellow-400 mr-1"></i>
                                    <span>4.7 Instructor Rating</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-400">
                                    <i class="fas fa-user-graduate mr-1"></i>
                                    <span>15,800 Students</span>
                                </div>
                                <div class="flex items-center text-sm text-gray-400">
                                    <i class="fas fa-play-circle mr-1"></i>
                                    <span>5 Courses</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="lg:col-span-1">
                <div class="sticky top-6">
                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6">
                        <h3 class="text-lg font-bold mb-4">This Course Includes:</h3>
                        <ul class="space-y-3">
                            <li class="flex items-center">
                                <i class="fas fa-video text-primary-600 mr-3"></i>
                                <span>40 hours on-demand video</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-file-alt text-primary-600 mr-3"></i>
                                <span>28 downloadable resources</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-tasks text-primary-600 mr-3"></i>
                                <span>15 practical exercises</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-certificate text-primary-600 mr-3"></i>
                                <span>Certificate of completion</span>
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-infinity text-primary-600 mr-3"></i>
                                <span>Full lifetime access</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 mb-6">
                        <h3 class="text-lg font-bold mb-4">Resources</h3>
                        <div class="space-y-4">
                            <a href="https://themeforest.net/search/digital%20marketing" class="flex items-center p-3 hover:bg-gray-700 rounded-lg transition">
                                <div class="bg-primary-600/20 text-primary-600 p-2 rounded-lg mr-3">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Digital Marketing Templates</h4>
                                    <p class="text-sm text-gray-400">PDF • 1.8MB</p>
                                </div>
                            </a>
                            
                            <a href="https://themeforest.net/search/digital%20marketing" class="flex items-center p-3 hover:bg-gray-700 rounded-lg transition">
                                <div class="bg-purple-600/20 text-purple-600 p-2 rounded-lg mr-3">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Analytics Dashboard</h4>
                                    <p class="text-sm text-gray-400">XLSX • 3.2MB</p>
                                </div>
                            </a>
                            
                            <a href="https://chat.whatsapp.com/CqW46y9Fkgh6RT09xO8ylV" class="flex items-center p-3 hover:bg-gray-700 rounded-lg transition">
                                <div class="bg-green-600/20 text-green-600 p-2 rounded-lg mr-3">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium">Marketing Community</h4>
                                    <p class="text-sm text-gray-400">Slack • 8K+ members</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    
                    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
                        <h3 class="text-lg font-bold mb-4">Students Also Viewed</h3>
                        <div class="space-y-4">
                            <a href="#" class="flex items-center hover:bg-gray-700 p-3 rounded-lg transition">
                                <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" 
                                     class="w-16 h-16 rounded-lg object-cover mr-3">
                                <div>
                                    <h4 class="font-medium">Google Ads Certification</h4>
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span>4.8 • 7,500 students</span>
                                    </div>
                                </div>
                            </a>
                            
                            <a href="#" class="flex items-center hover:bg-gray-700 p-3 rounded-lg transition">
                                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80" 
                                     class="w-16 h-16 rounded-lg object-cover mr-3">
                                <div>
                                    <h4 class="font-medium">Social Media Marketing</h4>
                                    <div class="flex items-center text-sm text-gray-400">
                                        <i class="fas fa-star text-yellow-400 mr-1"></i>
                                        <span>4.6 • 9,200 students</span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-gray-800 border-t border-gray-700 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-graduation-cap text-primary-600 text-2xl mr-2"></i>
                        <span class="text-xl font-bold">EduPro</span>
                    </div>
                    <p class="text-gray-400 mb-4">Advancing careers through world-class digital education.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Company</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Careers</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Press</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Support</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Feedback</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Accessibility</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-gray-200 uppercase tracking-wider mb-4">Legal</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Cookie Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">GDPR</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-gray-700 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm">© 2024 EduPro, Inc. All rights reserved.</p>
                <div class="mt-4 md:mt-0 flex space-x-6">
                    <a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Sitemap</a>
                    <a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Trademark</a>
                    <a href="#" class="text-gray-500 hover:text-gray-400 text-sm transition">Policies</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            updateProgress();
            
            const checkboxes = document.querySelectorAll('.lesson-checkbox');
            checkboxes.forEach(checkbox => {
                const lessonId = checkbox.id;
                const savedState = localStorage.getItem(lessonId);
                if (savedState === 'true') {
                    checkbox.checked = true;
                    const label = checkbox.nextElementSibling;
                    const checkIcon = label.querySelector('.fa-check');
                    if (checkIcon) {
                        checkIcon.classList.remove('hidden');
                    }
                }
                
                checkbox.addEventListener('change', function() {
                    localStorage.setItem(lessonId, this.checked);
                    
                    const label = this.nextElementSibling;
                    const checkIcon = label.querySelector('.fa-check');
                    if (this.checked) {
                        checkIcon.classList.remove('hidden');
                    } else {
                        checkIcon.classList.add('hidden');
                    }
                    
                    updateProgress();
                    updateCompletionBadge();
                });
            });
            
            updateCompletionBadge();
        });

        function updateProgress() {
            const checkboxes = document.querySelectorAll('.lesson-checkbox');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            const completed = [...checkboxes].filter(checkbox => checkbox.checked).length;
            const total = checkboxes.length;
            const progress = (completed / total) * 100;
            
            progressBar.style.width = progress + '%';
            progressText.textContent = `${completed}/${total} lessons completed`;
            
            if (progress < 30) {
                progressBar.className = 'bg-red-500 h-2.5 rounded-full progress-bar';
            } else if (progress < 70) {
                progressBar.className = 'bg-yellow-500 h-2.5 rounded-full progress-bar';
            } else {
                progressBar.className = 'bg-green-500 h-2.5 rounded-full progress-bar';
            }
            
            localStorage.setItem('courseProgress', progress);
        }
        
        function updateCompletionBadge() {
            const checkboxes = document.querySelectorAll('.lesson-checkbox');
            const completed = [...checkboxes].filter(checkbox => checkbox.checked).length;
            const completionBadge = document.getElementById('completion-badge');
            
            if (completed > 0) {
                completionBadge.classList.remove('hidden');
                completionBadge.textContent = completed;
            } else {
                completionBadge.classList.add('hidden');
            }
        }
    </script>
</body>
</html>