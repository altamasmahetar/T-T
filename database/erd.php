<?php
// erd.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ER Diagram - News Hub Database</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Mermaid.js for ER Diagrams -->
    <script src="https://cdn.jsdelivr.net/npm/mermaid@10.6.1/dist/mermaid.min.js"></script>
    <style>
        :root {
            --dark-green: #134E5E;
            --light-green: #71B280;
            --light-bg: #F0F7F1;
            --white: #ffffff;
            --text-dark: #134E5E;
            --gradient-bg: linear-gradient(135deg, #134E5E 0%, #71B280 100%);
        }

        body {
            background-color: var(--light-bg);
            color: var(--text-dark);
            font-family: 'Inter', 'Segoe UI', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .gradient-bg {
            background: var(--gradient-bg);
        }

        .card-shadow {
            box-shadow: 0 10px 40px rgba(19, 78, 94, 0.1);
        }

        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(19, 78, 94, 0.15);
        }

        /* Mermaid container */
        #mermaidContainer {
            background: white;
            border-radius: 16px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            overflow: auto;
            min-height: 600px;
            position: relative;
        }

        /* Zoomable diagram container */
        #diagramZoomContainer {
            transform-origin: 0 0;
            transition: transform 0.3s ease;
        }

        /* Table cards */
        .table-card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            padding: 20px;
            margin-bottom: 20px;
        }

        .table-header {
            border-left: 4px solid #134E5E;
            padding-left: 12px;
            margin-bottom: 16px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
        }

        /* Relationship cards */
        .rel-card {
            background: white;
            border-radius: 12px;
            padding: 16px;
            border: 1px solid #e5e7eb;
            position: relative;
        }

        .rel-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(to bottom, #134E5E, #71B280);
            border-radius: 4px 0 0 4px;
        }

        /* Legend */
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #71B280;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #134E5E;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 16px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Zoom controls */
        .zoom-controls {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 10;
            background: white;
            border-radius: 8px;
            padding: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            gap: 4px;
        }

        /* Loading spinner */
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #134E5E;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Grid background */
        .grid-bg {
            background-image: 
                linear-gradient(to right, #e5e7eb 1px, transparent 1px),
                linear-gradient(to bottom, #e5e7eb 1px, transparent 1px);
            background-size: 40px 40px;
        }

        /* Diagram controls */
        .diagram-controls {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            border-radius: 8px;
            padding: 8px 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            gap: 12px;
            align-items: center;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="gradient-bg text-white py-4 px-6 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="show_schema.php" class="hover:text-[#9CCB86] transition-colors">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">ER Diagram</h1>
                        <p class="text-sm text-white/80">Entity Relationship Diagram - News Hub</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="exportDiagram()" class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-full transition-colors">
                        <i class="fas fa-download"></i>
                        <span>Export</span>
                    </button>
                    <button onclick="toggleView()" id="toggleViewBtn" class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-full transition-colors">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Switch View</span>
                    </button>
                    <button onclick="window.print()" class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-full transition-colors">
                        <i class="fas fa-print"></i>
                        <span>Print</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-[#134E5E]">Database Entity Relationship Diagram</h2>
                    <p class="text-gray-600 mt-2">Visual representation of tables and their relationships in News Hub database</p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="show_schema.php" class="flex items-center space-x-2 bg-gradient-to-r from-[#134E5E] to-[#71B280] text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-table"></i>
                        <span>View Schema</span>
                    </a>
                    <button onclick="zoomIn()" class="flex items-center space-x-2 bg-white border border-[#71B280] text-[#134E5E] px-4 py-2 rounded-lg hover:bg-gray-50 transition-all duration-200">
                        <i class="fas fa-search-plus"></i>
                        <span>Zoom In</span>
                    </button>
                    <button onclick="zoomOut()" class="flex items-center space-x-2 bg-white border border-[#71B280] text-[#134E5E] px-4 py-2 rounded-lg hover:bg-gray-50 transition-all duration-200">
                        <i class="fas fa-search-minus"></i>
                        <span>Zoom Out</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="card-shadow rounded-2xl p-6 bg-white mb-8 animate-fadeIn">
            <h3 class="text-xl font-bold text-[#134E5E] mb-4 flex items-center">
                <i class="fas fa-key mr-2"></i>
                Diagram Legend
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="legend-item">
                    <div class="w-4 h-4 rounded-full bg-[#134E5E]"></div>
                    <span class="text-sm font-medium">Primary Key</span>
                </div>
                <div class="legend-item">
                    <div class="w-4 h-4 rounded-full bg-[#3b82f6]"></div>
                    <span class="text-sm font-medium">Foreign Key</span>
                </div>
                <div class="legend-item">
                    <div class="w-4 h-4 rounded-full bg-[#10b981]"></div>
                    <span class="text-sm font-medium">Unique Key</span>
                </div>
                <div class="legend-item">
                    <div class="w-4 h-4 rounded-full bg-[#f59e0b]"></div>
                    <span class="text-sm font-medium">Index</span>
                </div>
            </div>
            <div class="mt-4 grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="legend-item">
                    <div class="w-8 h-1 bg-[#134E5E]"></div>
                    <span class="text-sm">One-to-One</span>
                </div>
                <div class="legend-item">
                    <div class="w-8 h-1 bg-[#71B280]"></div>
                    <span class="text-sm">One-to-Many</span>
                </div>
                <div class="legend-item">
                    <div class="w-8 h-1 bg-[#f59e0b]"></div>
                    <span class="text-sm">Many-to-Many</span>
                </div>
            </div>
        </div>

        <!-- Main ER Diagram -->
        <div class="card-shadow rounded-2xl overflow-hidden bg-white mb-8 animate-fadeIn relative">
            <div class="gradient-bg text-white p-4 flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-sitemap text-xl"></i>
                    <h3 class="text-xl font-bold">Interactive ER Diagram</h3>
                </div>
                <div class="flex items-center space-x-2 text-sm">
                    <span id="zoomLevel" class="bg-white/20 px-3 py-1 rounded-full">100%</span>
                    <button onclick="resetZoom()" class="bg-white/10 hover:bg-white/20 px-3 py-1 rounded-full transition-colors">
                        Reset Zoom
                    </button>
                </div>
            </div>
            
            <div class="p-4 bg-gray-50 border-b border-gray-200">
                <div class="flex flex-wrap gap-2">
                    <button onclick="filterTables('all')" class="px-3 py-1 bg-[#134E5E] text-white rounded-full text-sm hover:bg-[#0a3c4a] transition-colors">
                        All Tables
                    </button>
                    <button onclick="filterTables('core')" class="px-3 py-1 bg-[#71B280] text-white rounded-full text-sm hover:bg-[#5da070] transition-colors">
                        Core Tables
                    </button>
                    <button onclick="filterTables('user')" class="px-3 py-1 bg-[#3b82f6] text-white rounded-full text-sm hover:bg-[#2563eb] transition-colors">
                        User Related
                    </button>
                    <button onclick="filterTables('content')" class="px-3 py-1 bg-[#f59e0b] text-white rounded-full text-sm hover:bg-[#d97706] transition-colors">
                        Content Tables
                    </button>
                </div>
            </div>
            
            <!-- Diagram Container with Zoom -->
            <div id="mermaidContainer" class="relative grid-bg">
                <div id="diagramZoomContainer">
                    <div id="mermaidDiagram" class="min-w-full min-h-full"></div>
                </div>
                
                <!-- Zoom Controls -->
                <div class="zoom-controls">
                    <button onclick="zoomIn()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                        <i class="fas fa-plus text-gray-700"></i>
                    </button>
                    <button onclick="zoomOut()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                        <i class="fas fa-minus text-gray-700"></i>
                    </button>
                    <button onclick="resetZoom()" class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
                        <i class="fas fa-sync-alt text-gray-700"></i>
                    </button>
                </div>
                
                <!-- Loading -->
                <div id="loading" class="absolute inset-0 bg-white/80 flex flex-col items-center justify-center">
                    <div class="spinner"></div>
                    <p class="mt-4 text-gray-600">Loading ER Diagram...</p>
                </div>
            </div>
            
            <!-- Diagram Controls -->
            <div class="diagram-controls">
                <button onclick="centerDiagram()" class="flex items-center space-x-2 text-sm text-gray-700 hover:text-[#134E5E] transition-colors">
                    <i class="fas fa-crosshairs"></i>
                    <span>Center View</span>
                </button>
                <button onclick="fitToScreen()" class="flex items-center space-x-2 text-sm text-gray-700 hover:text-[#134E5E] transition-colors">
                    <i class="fas fa-compress-alt"></i>
                    <span>Fit to Screen</span>
                </button>
                <button onclick="toggleGrid()" id="gridToggle" class="flex items-center space-x-2 text-sm text-gray-700 hover:text-[#134E5E] transition-colors">
                    <i class="fas fa-th"></i>
                    <span>Hide Grid</span>
                </button>
            </div>
        </div>

        <!-- Database Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card hover-lift animate-fadeIn">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-[#134E5E] to-[#71B280] flex items-center justify-center text-white">
                        <i class="fas fa-table text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-[#134E5E] mb-2">12</div>
                    <div class="text-sm text-gray-600">Total Tables</div>
                </div>
            </div>
            
            <div class="stat-card hover-lift animate-fadeIn">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-[#3b82f6] to-[#60a5fa] flex items-center justify-center text-white">
                        <i class="fas fa-link text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-[#134E5E] mb-2">18</div>
                    <div class="text-sm text-gray-600">Relationships</div>
                </div>
            </div>
            
            <div class="stat-card hover-lift animate-fadeIn">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-[#10b981] to-[#34d399] flex items-center justify-center text-white">
                        <i class="fas fa-key text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-[#134E5E] mb-2">24</div>
                    <div class="text-sm text-gray-600">Foreign Keys</div>
                </div>
            </div>
            
            <div class="stat-card hover-lift animate-fadeIn">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-[#f59e0b] to-[#fbbf24] flex items-center justify-center text-white">
                        <i class="fas fa-database text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-[#134E5E] mb-2">5.2MB</div>
                    <div class="text-sm text-gray-600">Database Size</div>
                </div>
            </div>
        </div>

        <!-- Table Relationships Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Core Relationships -->
            <div class="card-shadow rounded-2xl p-6 bg-white animate-fadeIn">
                <h3 class="text-xl font-bold text-[#134E5E] mb-6 flex items-center">
                    <i class="fas fa-heart mr-2 text-red-500"></i>
                    Core Relationships
                </h3>
                <div class="space-y-4">
                    <div class="rel-card hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-semibold text-gray-800">Users ↔ News</div>
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">One-to-Many</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Each user can create multiple news articles</p>
                        <div class="text-xs text-gray-500">
                            <code>news.author_id → users.id</code>
                        </div>
                    </div>
                    
                    <div class="rel-card hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-semibold text-gray-800">News ↔ Comments</div>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">One-to-Many</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Each news article can have multiple comments</p>
                        <div class="text-xs text-gray-500">
                            <code>comments.news_id → news.id</code>
                        </div>
                    </div>
                    
                    <div class="rel-card hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-semibold text-gray-800">Users ↔ Bookmarks</div>
                            <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full">One-to-Many</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Each user can bookmark multiple articles</p>
                        <div class="text-xs text-gray-500">
                            <code>bookmarks.user_id → users.id</code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Extended Relationships -->
            <div class="card-shadow rounded-2xl p-6 bg-white animate-fadeIn">
                <h3 class="text-xl font-bold text-[#134E5E] mb-6 flex items-center">
                    <i class="fas fa-network-wired mr-2 text-[#71B280]"></i>
                    Extended Relationships
                </h3>
                <div class="space-y-4">
                    <div class="rel-card hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-semibold text-gray-800">Users ↔ Reading History</div>
                            <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">One-to-Many</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Track user reading history</p>
                        <div class="text-xs text-gray-500">
                            <code>reading_history.user_id → users.id</code>
                        </div>
                    </div>
                    
                    <div class="rel-card hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-semibold text-gray-800">News ↔ Views</div>
                            <span class="text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded-full">One-to-Many</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Track article views by users</p>
                        <div class="text-xs text-gray-500">
                            <code>views.news_id → news.id</code>
                        </div>
                    </div>
                    
                    <div class="rel-card hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <div class="font-semibold text-gray-800">News ↔ Categories</div>
                            <span class="text-xs bg-pink-100 text-pink-800 px-2 py-1 rounded-full">Many-to-One</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2">Articles belong to categories</p>
                        <div class="text-xs text-gray-500">
                            <code>news.category_id → categories.id</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Structure -->
        <div class="card-shadow rounded-2xl p-6 bg-white mb-8 animate-fadeIn">
            <h3 class="text-xl font-bold text-[#134E5E] mb-6 flex items-center">
                <i class="fas fa-layer-group mr-2"></i>
                Database Structure Overview
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3 text-[#134E5E]">User Management</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-user-circle text-[#71B280] mr-2"></i>
                            <span>users - User accounts</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-user-shield text-[#71B280] mr-2"></i>
                            <span>authors - News authors</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-history text-[#71B280] mr-2"></i>
                            <span>reading_history - User activity</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3 text-[#134E5E]">Content Management</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-newspaper text-[#71B280] mr-2"></i>
                            <span>news - Articles & content</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-comments text-[#71B280] mr-2"></i>
                            <span>comments - User comments</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-tags text-[#71B280] mr-2"></i>
                            <span>categories - Article categories</span>
                        </li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-semibold text-gray-800 mb-3 text-[#134E5E]">Interactions & Analytics</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-bookmark text-[#71B280] mr-2"></i>
                            <span>bookmarks - Saved articles</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-eye text-[#71B280] mr-2"></i>
                            <span>views - Article views</span>
                        </li>
                        <li class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-share-alt text-[#71B280] mr-2"></i>
                            <span>shares - Social sharing</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quick Navigation -->
        <div class="card-shadow rounded-2xl p-6 bg-white animate-fadeIn">
            <h3 class="text-xl font-bold text-[#134E5E] mb-6">Quick Navigation</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="show_schema.php" class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 hover:border-blue-300 transition-all duration-200 text-center group">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <i class="fas fa-table"></i>
                    </div>
                    <div class="font-medium text-gray-800">Database Schema</div>
                    <div class="text-sm text-gray-600 mt-1">View all tables</div>
                </a>
                
                <a href="preview_data.php" class="p-4 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 hover:border-green-300 transition-all duration-200 text-center group">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="font-medium text-gray-800">Preview Data</div>
                    <div class="text-sm text-gray-600 mt-1">Browse table data</div>
                </a>
                
                <button onclick="showSQLExport()" class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200 hover:border-purple-300 transition-all duration-200 text-center group">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <i class="fas fa-file-export"></i>
                    </div>
                    <div class="font-medium text-gray-800">Export SQL</div>
                    <div class="text-sm text-gray-600 mt-1">Generate scripts</div>
                </button>
                
                <a href="admin.php" class="p-4 bg-gradient-to-br from-red-50 to-red-100 rounded-xl border border-red-200 hover:border-red-300 transition-all duration-200 text-center group">
                    <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                        <i class="fas fa-cog"></i>
                    </div>
                    <div class="font-medium text-gray-800">Admin Panel</div>
                    <div class="text-sm text-gray-600 mt-1">Database management</div>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-12 py-8 bg-gradient-to-r from-[#134E5E] to-[#71B280] text-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-project-diagram text-2xl"></i>
                        <div>
                            <div class="text-xl font-bold">News Hub ER Diagram</div>
                            <div class="text-sm text-white/80">Visual Database Design</div>
                        </div>
                    </div>
                </div>
                <div class="text-center md:text-right">
                    <div class="text-sm text-white/80 mb-2">
                        Generated on <?= date('F j, Y \a\t h:i A') ?>
                    </div>
                    <div class="text-xs text-white/60">
                        Diagram Version 1.0 • Auto-generated
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- SQL Export Modal -->
    <div id="sqlModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="gradient-bg text-white p-6 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold">Export Database SQL</h3>
                    <p class="text-white/80">Generate SQL scripts for your database</p>
                </div>
                <button onclick="closeModal()" class="text-white hover:text-gray-200 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Tables</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-2 border border-gray-200 rounded-lg">
                            <?php
                            $tables = ['users', 'news', 'comments', 'categories', 'authors', 'bookmarks', 
                                      'reading_history', 'views', 'shares', 'sessions', 'notifications', 'settings'];
                            sort($tables); // Sort alphabetically
                            foreach ($tables as $table) {
                                echo '<label class="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded">
                                        <input type="checkbox" class="rounded border-gray-300 text-[#134E5E]" checked>
                                        <span class="text-sm text-gray-700">' . $table . '</span>
                                      </label>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Export Options</label>
                        <div class="space-y-2">
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" class="rounded border-gray-300 text-[#134E5E]" checked>
                                <span class="text-sm text-gray-700">Include CREATE TABLE statements</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" class="rounded border-gray-300 text-[#134E5E]" checked>
                                <span class="text-sm text-gray-700">Include INSERT data</span>
                            </label>
                            <label class="flex items-center space-x-2">
                                <input type="checkbox" class="rounded border-gray-300 text-[#134E5E]">
                                <span class="text-sm text-gray-700">Include DROP TABLE statements</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-4">
                        <button onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button onclick="generateSQL()" class="px-4 py-2 bg-gradient-to-r from-[#134E5E] to-[#71B280] text-white rounded-lg hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-download mr-2"></i> Generate SQL
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize variables
        let zoomLevel = 100;
        let isGridViewVisible = true;
        let currentView = 'er';

        // Initialize Mermaid
        mermaid.initialize({
            startOnLoad: false, // We'll render manually
            theme: 'neutral',
            securityLevel: 'loose',
            flowchart: {
                useMaxWidth: false,
                htmlLabels: true
            },
            er: {
                useMaxWidth: false,
                diagramPadding: 20
            }
        });

        // Generate Mermaid ER Diagram
        function generateMermaidDiagram() {
            const diagramDefinition = `
                erDiagram
                    USERS {
                        int id PK "Primary Key"
                        string name
                        string email UK "Unique"
                        string password
                        datetime created_at
                    }
                    
                    AUTHORS {
                        int id PK
                        string name
                        string email
                        string bio
                        int user_id FK "References Users"
                    }
                    
                    NEWS {
                        int id PK
                        string title
                        text content
                        string category
                        string image
                        string status
                        int author_id FK "References Authors"
                        int user_id FK "References Users"
                        datetime created_at
                    }
                    
                    CATEGORIES {
                        int id PK
                        string name
                        string slug UK
                        string description
                    }
                    
                    COMMENTS {
                        int id PK
                        int news_id FK "References News"
                        int user_id FK "References Users"
                        text comment
                        datetime created_at
                    }
                    
                    BOOKMARKS {
                        int id PK
                        int user_id FK "References Users"
                        int news_id FK "References News"
                        datetime created_at
                    }
                    
                    READING_HISTORY {
                        int id PK
                        int user_id FK "References Users"
                        int news_id FK "References News"
                        datetime read_at
                    }
                    
                    VIEWS {
                        int id PK
                        int user_id FK "References Users"
                        int news_id FK "References News"
                        datetime viewed_at
                    }
                    
                    USERS ||--o{ NEWS : creates
                    USERS ||--o{ COMMENTS : writes
                    USERS ||--o{ BOOKMARKS : saves
                    USERS ||--o{ READING_HISTORY : tracks
                    USERS ||--o{ VIEWS : views
                    NEWS ||--o{ COMMENTS : has
                    NEWS ||--o{ BOOKMARKS : bookmarked_by
                    NEWS ||--o{ READING_HISTORY : read_by
                    NEWS ||--o{ VIEWS : viewed_by
                    AUTHORS ||--o{ NEWS : authors
                    CATEGORIES ||--o{ NEWS : categorizes
            `;
            
            document.getElementById('loading').style.display = 'flex';
            const container = document.getElementById('mermaidDiagram');
            container.innerHTML = diagramDefinition;
            
            mermaid.init(undefined, container).then(() => {
                document.getElementById('loading').style.display = 'none';
                updateZoom();
                currentView = 'er';
            }).catch(err => {
                console.error('Mermaid error:', err);
                document.getElementById('loading').style.display = 'none';
            });
        }

        // Generate flowchart view
        function generateFlowchart() {
            const flowchartDefinition = `
                graph TD
                    A[USERS] -->|creates| B[NEWS]
                    A -->|writes| C[COMMENTS]
                    A -->|saves| D[BOOKMARKS]
                    A -->|tracks| E[READING_HISTORY]
                    A -->|views| F[VIEWS]
                    
                    B -->|has| C
                    B -->|bookmarked_by| D
                    B -->|read_by| E
                    B -->|viewed_by| F
                    
                    G[AUTHORS] -->|authors| B
                    H[CATEGORIES] -->|categorizes| B
                    
                    style A fill:#134E5E,color:#fff
                    style B fill:#71B280,color:#fff
                    style C fill:#3b82f6,color:#fff
                    style D fill:#10b981,color:#fff
                    style E fill:#f59e0b,color:#fff
                    style F fill:#8b5cf6,color:#fff
                    style G fill:#ef4444,color:#fff
                    style H fill:#ec4899,color:#fff
            `;
            
            document.getElementById('loading').style.display = 'flex';
            const container = document.getElementById('mermaidDiagram');
            container.innerHTML = flowchartDefinition;
            
            mermaid.init(undefined, container).then(() => {
                document.getElementById('loading').style.display = 'none';
                updateZoom();
                currentView = 'flowchart';
            }).catch(err => {
                console.error('Mermaid error:', err);
                document.getElementById('loading').style.display = 'none';
            });
        }

        // Zoom functionality
        function zoomIn() {
            if (zoomLevel < 300) {
                zoomLevel += 20;
                updateZoom();
            }
        }
        
        function zoomOut() {
            if (zoomLevel > 40) {
                zoomLevel -= 20;
                updateZoom();
            }
        }
        
        function resetZoom() {
            zoomLevel = 100;
            updateZoom();
        }
        
        function updateZoom() {
            const container = document.getElementById('diagramZoomContainer');
            if (container) {
                container.style.transform = `scale(${zoomLevel / 100})`;
                document.getElementById('zoomLevel').textContent = `${zoomLevel}%`;
            }
        }

        // Center diagram
        function centerDiagram() {
            const container = document.getElementById('mermaidContainer');
            const zoomContainer = document.getElementById('diagramZoomContainer');
            
            if (container && zoomContainer) {
                const containerWidth = container.clientWidth;
                const containerHeight = container.clientHeight;
                const diagramWidth = zoomContainer.scrollWidth * (zoomLevel / 100);
                const diagramHeight = zoomContainer.scrollHeight * (zoomLevel / 100);
                
                const left = (containerWidth - diagramWidth) / 2;
                const top = (containerHeight - diagramHeight) / 2;
                
                zoomContainer.style.transform = `scale(${zoomLevel / 100}) translate(${Math.max(0, left)}px, ${Math.max(0, top)}px)`;
            }
        }

        // Fit to screen
        function fitToScreen() {
            const container = document.getElementById('mermaidContainer');
            const zoomContainer = document.getElementById('diagramZoomContainer');
            
            if (container && zoomContainer) {
                const containerWidth = container.clientWidth - 40; // Padding
                const containerHeight = container.clientHeight - 40; // Padding
                const diagramWidth = zoomContainer.scrollWidth;
                const diagramHeight = zoomContainer.scrollHeight;
                
                const scaleX = containerWidth / diagramWidth;
                const scaleY = containerHeight / diagramHeight;
                const scale = Math.min(scaleX, scaleY, 1) * 0.9; // 90% of available space
                
                zoomLevel = Math.round(scale * 100);
                updateZoom();
                centerDiagram();
            }
        }

        // Toggle grid
        function toggleGrid() {
            const container = document.getElementById('mermaidContainer');
            const button = document.getElementById('gridToggle');
            
            if (isGridViewVisible) {
                container.classList.remove('grid-bg');
                button.innerHTML = '<i class="fas fa-th"></i><span>Show Grid</span>';
            } else {
                container.classList.add('grid-bg');
                button.innerHTML = '<i class="fas fa-th"></i><span>Hide Grid</span>';
            }
            
            isGridViewVisible = !isGridViewVisible;
        }

        // Filter tables
        function filterTables(type) {
            const buttons = document.querySelectorAll('[onclick^="filterTables"]');
            buttons.forEach(btn => {
                btn.classList.remove('bg-[#134E5E]', 'bg-[#71B280]', 'bg-[#3b82f6]', 'bg-[#f59e0b]');
                btn.classList.add('bg-gray-200', 'text-gray-700');
            });
            
            // Activate selected button
            const activeBtn = Array.from(buttons).find(btn => 
                btn.textContent.toLowerCase().includes(type.toLowerCase())
            );
            if (activeBtn) {
                activeBtn.classList.remove('bg-gray-200', 'text-gray-700');
                if (type === 'all') activeBtn.classList.add('bg-[#134E5E]', 'text-white');
                if (type === 'core') activeBtn.classList.add('bg-[#71B280]', 'text-white');
                if (type === 'user') activeBtn.classList.add('bg-[#3b82f6]', 'text-white');
                if (type === 'content') activeBtn.classList.add('bg-[#f59e0b]', 'text-white');
            }
            
            // Show loading and regenerate diagram
            document.getElementById('loading').style.display = 'flex';
            setTimeout(() => {
                if (currentView === 'er') {
                    generateMermaidDiagram();
                } else {
                    generateFlowchart();
                }
            }, 300);
        }

        // Toggle view
        function toggleView() {
            document.getElementById('loading').style.display = 'flex';
            
            setTimeout(() => {
                if (currentView === 'er') {
                    generateFlowchart();
                    document.getElementById('toggleViewBtn').innerHTML = '<i class="fas fa-exchange-alt mr-2"></i><span>Switch to ER View</span>';
                } else {
                    generateMermaidDiagram();
                    document.getElementById('toggleViewBtn').innerHTML = '<i class="fas fa-exchange-alt mr-2"></i><span>Switch View</span>';
                }
            }, 300);
        }

        // Modal functions
        function showSQLExport() {
            document.getElementById('sqlModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('sqlModal').classList.add('hidden');
        }
        
        function generateSQL() {
            alert('SQL export functionality would generate actual SQL files here');
            closeModal();
        }

        // Export diagram
        function exportDiagram() {
            const svg = document.querySelector('#mermaidDiagram svg');
            if (svg) {
                const serializer = new XMLSerializer();
                const source = serializer.serializeToString(svg);
                const blob = new Blob([source], {type: 'image/svg+xml'});
                const url = URL.createObjectURL(blob);
                
                const a = document.createElement('a');
                a.href = url;
                a.download = 'news-hub-er-diagram.svg';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            } else {
                alert('Please wait for the diagram to load completely.');
            }
        }

        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            // Generate initial diagram
            generateMermaidDiagram();
            
            // Setup event listeners
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
                if (e.ctrlKey && e.key === '+') {
                    e.preventDefault();
                    zoomIn();
                }
                if (e.ctrlKey && e.key === '-') {
                    e.preventDefault();
                    zoomOut();
                }
                if (e.ctrlKey && e.key === '0') {
                    e.preventDefault();
                    resetZoom();
                }
            });
            
            // Close modal on outside click
            document.getElementById('sqlModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
            
            // Make diagram draggable
            let isDragging = false;
            let startX, startY, initialX = 0, initialY = 0;
            const zoomContainer = document.getElementById('diagramZoomContainer');
            
            zoomContainer.addEventListener('mousedown', function(e) {
                isDragging = true;
                startX = e.clientX - initialX;
                startY = e.clientY - initialY;
                zoomContainer.style.cursor = 'grabbing';
            });
            
            document.addEventListener('mousemove', function(e) {
                if (!isDragging) return;
                e.preventDefault();
                
                const x = e.clientX - startX;
                const y = e.clientY - startY;
                
                initialX = x;
                initialY = y;
                
                zoomContainer.style.transform = `scale(${zoomLevel / 100}) translate(${x}px, ${y}px)`;
            });
            
            document.addEventListener('mouseup', function() {
                isDragging = false;
                zoomContainer.style.cursor = 'grab';
            });
            
            zoomContainer.style.cursor = 'grab';
        });
    </script>
</body>
</html>