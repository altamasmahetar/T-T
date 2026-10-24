<?php
// show_schema.php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Schema - News Hub</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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

        /* Table Styling */
        .schema-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }

        .schema-table th {
            background: linear-gradient(135deg, #134E5E 0%, #71B280 100%);
            color: white;
            font-weight: 600;
            padding: 16px;
            text-align: left;
            border-bottom: 2px solid #0a3c4a;
        }

        .schema-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
            color: #4b5563;
        }

        .schema-table tr:last-child td {
            border-bottom: none;
        }

        .schema-table tr:hover {
            background-color: #f9fafb;
        }

        /* Type badges */
        .type-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            margin: 2px 0;
        }

        .type-int {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .type-varchar {
            background: #dcfce7;
            color: #166534;
        }

        .type-text {
            background: #fef3c7;
            color: #92400e;
        }

        .type-datetime {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .type-enum {
            background: #fce7f3;
            color: #be185d;
        }

        /* Key badges */
        .key-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin: 2px 0;
        }

        .key-pri {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            color: white;
        }

        .key-for {
            background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
            color: white;
        }

        .key-uni {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            color: white;
        }

        /* Table card */
        .table-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 24px;
            border: 1px solid #e5e7eb;
        }

        .table-header {
            background: linear-gradient(135deg, #134E5E 0%, #71B280 100%);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        /* Stats cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out;
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

        /* Responsive */
        @media (max-width: 768px) {
            .schema-table {
                display: block;
                overflow-x: auto;
            }
            
            .schema-table th,
            .schema-table td {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="gradient-bg text-white py-4 px-6 shadow-lg">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <a href="../guest-index.php" class="hover:text-[#9CCB86] transition-colors">
                        <i class="fas fa-arrow-left text-xl"></i>
                    </a>
                    <div class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center">
                        <i class="fas fa-database"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">Database Schema</h1>
                        <p class="text-sm text-white/80">News Hub Database Structure</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="admin.php" class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-full transition-colors">
                        <i class="fas fa-cog"></i>
                        <span>Admin Panel</span>
                    </a>
                    <button onclick="window.print()" class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-full transition-colors">
                        <i class="fas fa-print"></i>
                        <span>Print</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <?php
            // 1. Database connection
            $host = 'localhost';
            $user = 'root';
            $pass = '';
            $dbname = 'travel_website';

            $conn = new mysqli($host, $user, $pass, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Get database stats
            $total_tables = $conn->query("SHOW TABLES")->num_rows;
            
            // Store tables result for reuse
            $tables_result_for_stats = $conn->query("SHOW TABLES");
            $total_rows = 0;
            while ($table = $tables_result_for_stats->fetch_array()) {
                $table_name = $table[0];
                $count_result = $conn->query("SELECT COUNT(*) as count FROM $table_name");
                $total_rows += $count_result->fetch_assoc()['count'];
            }
            
            // Re-query for columns
            $tables_result_for_columns = $conn->query("SHOW TABLES");
            $total_columns = 0;
            while ($table = $tables_result_for_columns->fetch_array()) {
                $table_name = $table[0];
                $columns_result = $conn->query("DESCRIBE $table_name");
                $total_columns += $columns_result->num_rows;
            }
            ?>
            <div class="stat-card hover-lift animate-fadeIn">
                <div class="stat-icon gradient-bg text-white">
                    <i class="fas fa-database"></i>
                </div>
                <div class="text-3xl font-bold text-[#134E5E] mb-2"><?= $dbname ?></div>
                <p class="text-gray-600">Database Name</p>
            </div>

            <div class="stat-card hover-lift animate-fadeIn">
                <div class="stat-icon bg-gradient-to-br from-[#f59e0b] to-[#fbbf24] text-white">
                    <i class="fas fa-table"></i>
                </div>
                <div class="text-3xl font-bold text-[#134E5E] mb-2"><?= $total_tables ?></div>
                <p class="text-gray-600">Total Tables</p>
            </div>

            <div class="stat-card hover-lift animate-fadeIn">
                <div class="stat-icon bg-gradient-to-br from-[#3b82f6] to-[#60a5fa] text-white">
                    <i class="fas fa-columns"></i>
                </div>
                <div class="text-3xl font-bold text-[#134E5E] mb-2"><?= $total_columns ?></div>
                <p class="text-gray-600">Total Columns</p>
            </div>

            <div class="stat-card hover-lift animate-fadeIn">
                <div class="stat-icon bg-gradient-to-br from-[#10b981] to-[#34d399] text-white">
                    <i class="fas fa-layer-group"></i>
                </div>
                <div class="text-3xl font-bold text-[#134E5E] mb-2"><?= number_format($total_rows) ?></div>
                <p class="text-gray-600">Total Records</p>
            </div>
        </div>

        <!-- Database Info Card -->
        <div class="card-shadow rounded-2xl p-6 mb-8 bg-white animate-fadeIn">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-[#134E5E]">
                    <i class="fas fa-info-circle mr-2"></i>
                    Database Information
                </h2>
                <span class="text-sm text-gray-500">Last Updated: <?= date('F j, Y H:i:s') ?></span>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Connection Details</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Host</span>
                            <span class="font-mono font-semibold text-[#134E5E]"><?= $host ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Username</span>
                            <span class="font-mono font-semibold text-[#134E5E]"><?= $user ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Database</span>
                            <span class="font-mono font-semibold text-[#134E5E]"><?= $dbname ?></span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Server Status</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Connection</span>
                            <span class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                                <span class="font-semibold text-green-600">Connected</span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">MySQL Version</span>
                            <span class="font-mono font-semibold text-[#134E5E]"><?= $conn->server_version ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Character Set</span>
                            <span class="font-mono font-semibold text-[#134E5E]"><?= $conn->character_set_name() ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables List -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-[#134E5E]">
                    <i class="fas fa-list-alt mr-2"></i>
                    Database Tables
                </h2>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" 
                               id="tableSearch" 
                               placeholder="Search tables..." 
                               class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#71B280] focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <button onclick="toggleAllTables()" id="toggleAllBtn" class="bg-gradient-to-r from-[#134E5E] to-[#71B280] text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all duration-200">
                        <i class="fas fa-chevron-down mr-2"></i> Expand All
                    </button>
                </div>
            </div>

            <?php
            // Get all tables
            $tables_query = $conn->query("SHOW TABLES");
            $table_index = 0;
            
            while ($table = $tables_query->fetch_array()) {
                $table_name = $table[0];
                $table_index++;
                
                // Get row count for this table
                $count_result = $conn->query("SELECT COUNT(*) as count FROM $table_name");
                $row_count = $count_result->fetch_assoc()['count'];
                
                // Get column count
                $columns_result = $conn->query("DESCRIBE $table_name");
                $column_count = $columns_result->num_rows;
                
                // Store columns data for later use
                $columns_data = [];
                while ($col = $columns_result->fetch_assoc()) {
                    $columns_data[] = $col;
                }
                ?>

                
                
                <div class="table-card hover-lift animate-fadeIn" style="animation-delay: <?= $table_index * 0.1 ?>s" data-table-name="<?= strtolower($table_name) ?>">
                    <div class="table-header cursor-pointer" onclick="toggleTable('<?= $table_name ?>')">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                                <i class="fas fa-table text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold"><?= $table_name ?></h3>
                                <p class="text-white/80 text-sm">
                                    <?= $column_count ?> columns • <?= number_format($row_count) ?> records
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="table-count">
                                <i class="fas fa-hashtag mr-1"></i> <?= $table_index ?>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-white/80">Click to expand</span>
                                <i id="icon-<?= $table_name ?>" class="fas fa-chevron-down transform transition-transform"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div id="content-<?= $table_name ?>" class="hidden p-6">
                        <div class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-columns text-blue-600 text-xl mr-3"></i>
                                        <div>
                                            <div class="text-2xl font-bold text-blue-600"><?= $column_count ?></div>
                                            <div class="text-sm text-blue-800">Columns</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-green-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-database text-green-600 text-xl mr-3"></i>
                                        <div>
                                            <div class="text-2xl font-bold text-green-600"><?= number_format($row_count) ?></div>
                                            <div class="text-sm text-green-800">Records</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-purple-50 rounded-lg">
                                    <div class="flex items-center">
                                        <i class="fas fa-key text-purple-600 text-xl mr-3"></i>
                                        <div>
                                            <div class="text-2xl font-bold text-purple-600">
                                                <?php
                                                // Count primary keys
                                                $pk_count = 0;
                                                $pk_result = $conn->query("SHOW INDEX FROM $table_name WHERE Key_name = 'PRIMARY'");
                                                $pk_count = $pk_result->num_rows;
                                                echo $pk_count;
                                                ?>
                                            </div>
                                            <div class="text-sm text-purple-800">Primary Keys</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="schema-table">
                                <thead>
                                    <tr>
                                        <th>Field</th>
                                        <th>Type</th>
                                        <th>Null</th>
                                        <th>Key</th>
                                        <th>Default</th>
                                        <th>Extra</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($columns_data as $col) {
                                        $type_class = 'type-varchar';
                                        if (strpos($col['Type'], 'int') !== false) $type_class = 'type-int';
                                        elseif (strpos($col['Type'], 'varchar') !== false) $type_class = 'type-varchar';
                                        elseif (strpos($col['Type'], 'text') !== false) $type_class = 'type-text';
                                        elseif (strpos($col['Type'], 'datetime') !== false || strpos($col['Type'], 'timestamp') !== false) $type_class = 'type-datetime';
                                        elseif (strpos($col['Type'], 'enum') !== false) $type_class = 'type-enum';
                                        
                                        $key_badge = '';
                                        if ($col['Key'] == 'PRI') $key_badge = '<span class="key-badge key-pri">PRIMARY</span>';
                                        elseif ($col['Key'] == 'MUL') $key_badge = '<span class="key-badge key-for">FOREIGN</span>';
                                        elseif ($col['Key'] == 'UNI') $key_badge = '<span class="key-badge key-uni">UNIQUE</span>';
                                        ?>
                                        <tr>
                                            <td class="font-semibold text-gray-900">
                                                <i class="fas fa-columns text-gray-400 mr-2"></i>
                                                <?= $col['Field'] ?>
                                            </td>
                                            <td>
                                                <span class="type-badge <?= $type_class ?>">
                                                    <?= strtoupper($col['Type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?= $col['Null'] == 'YES' 
                                                    ? '<span class="text-red-600 font-semibold">YES</span>' 
                                                    : '<span class="text-green-600 font-semibold">NO</span>' ?>
                                            </td>
                                            <td><?= $key_badge ?></td>
                                            <td>
                                                <code class="bg-gray-100 px-2 py-1 rounded text-sm">
                                                    <?= $col['Default'] ?? 'NULL' ?>
                                                </code>
                                            </td>
                                            <td>
                                                <?php if ($col['Extra']): ?>
                                                    <span class="text-sm font-medium text-[#134E5E]">
                                                        <?= strtoupper($col['Extra']) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-gray-400">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Table Actions -->
                        <div class="mt-6 flex justify-between items-center">
                            <div class="text-sm text-gray-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                Last updated: Just now
                            </div>
                            <div class="flex space-x-2">
                                <button onclick="showSQL('<?= $table_name ?>')" class="flex items-center space-x-2 bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg transition-colors">
                                    <i class="fas fa-code"></i>
                                    <span>Show SQL</span>
                                </button>
                                <button onclick="previewData('<?= $table_name ?>')" class="flex items-center space-x-2 bg-gradient-to-r from-[#134E5E] to-[#71B280] text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all duration-200">
                                    <i class="fas fa-eye"></i>
                                    <span>Preview Data</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- Database Relationships -->
        <div class="card-shadow rounded-2xl p-6 bg-white animate-fadeIn mb-8">
            <h2 class="text-2xl font-bold text-[#134E5E] mb-6">
                <i class="fas fa-project-diagram mr-2"></i>
                Database Relationships
            </h2>
            
            <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl p-8 border border-gray-200">
                <div class="text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-[#134E5E] to-[#71B280] flex items-center justify-center">
                        <i class="fas fa-link text-2xl text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Relationship Diagram</h3>
                    <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                        The database follows a relational structure with foreign key relationships between tables
                        for efficient data management and integrity.
                    </p>
                    
                    <!-- Relationship Graph -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="p-4 bg-white rounded-lg border border-gray-200 text-center">
                            <div class="text-lg font-semibold text-[#134E5E] mb-1">Users</div>
                            <div class="text-sm text-gray-600">Central table</div>
                        </div>
                        <div class="p-4 bg-white rounded-lg border border-gray-200 text-center">
                            <div class="text-lg font-semibold text-[#134E5E] mb-1">News</div>
                            <div class="text-sm text-gray-600">Main content</div>
                        </div>
                        <div class="p-4 bg-white rounded-lg border border-gray-200 text-center">
                            <div class="text-lg font-semibold text-[#134E5E] mb-1">Comments</div>
                            <div class="text-sm text-gray-600">User interactions</div>
                        </div>
                        <div class="p-4 bg-white rounded-lg border border-gray-200 text-center">
                            <div class="text-lg font-semibold text-[#134E5E] mb-1">Bookmarks</div>
                            <div class="text-sm text-gray-600">User preferences</div>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex justify-center">
                        <a href="erd.php" class="flex items-center space-x-2 bg-gradient-to-r from-[#134E5E] to-[#71B280] text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-200">
                            <i class="fas fa-sitemap"></i>
                            <span>View Complete ER Diagram</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card-shadow rounded-2xl p-6 bg-white animate-fadeIn">
            <h2 class="text-2xl font-bold text-[#134E5E] mb-6">
                <i class="fas fa-bolt mr-2"></i>
                Quick Actions
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <button onclick="exportSchema()" class="p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border border-blue-200 hover:border-blue-300 transition-all duration-200 group">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <i class="fas fa-file-export"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-800">Export Schema</div>
                            <div class="text-sm text-gray-600">Download as SQL file</div>
                        </div>
                    </div>
                </button>
                
                <button onclick="generateDocumentation()" class="p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-xl border border-green-200 hover:border-green-300 transition-all duration-200 group">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-800">Generate Docs</div>
                            <div class="text-sm text-gray-600">Create documentation</div>
                        </div>
                    </div>
                </button>
                
                <button onclick="optimizeDatabase()" class="p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl border border-purple-200 hover:border-purple-300 transition-all duration-200 group">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-500 to-purple-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div class="text-left">
                            <div class="font-semibold text-gray-800">Optimize DB</div>
                            <div class="text-sm text-gray-600">Run optimization</div>
                        </div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-12 py-8 bg-gradient-to-r from-[#134E5E] to-[#71B280] text-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <div class="flex items-center space-x-2">
                        <i class="fas fa-database text-2xl"></i>
                        <div>
                            <div class="text-xl font-bold">News Hub Database</div>
                            <div class="text-sm text-white/80">Schema Viewer v1.0</div>
                        </div>
                    </div>
                </div>
                <div class="text-center md:text-right">
                    <div class="text-sm text-white/80 mb-2">
                        Generated on <?= date('F j, Y \a\t h:i A') ?>
                    </div>
                    <div class="text-xs text-white/60">
                        Total Size: <?php 
                            $size_result = $conn->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb FROM information_schema.tables WHERE table_schema = '$dbname'");
                            $size = $size_result->fetch_assoc()['size_mb'];
                            echo $size . ' MB';
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- SQL Modal -->
    <div id="sqlModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl w-full max-w-4xl mx-4 max-h-[80vh] overflow-hidden">
            <div class="gradient-bg text-white p-6 flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold">SQL Structure</h3>
                    <p id="modalTableName" class="text-white/80"></p>
                </div>
                <button onclick="closeModal()" class="text-white hover:text-gray-200 text-2xl">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <pre id="sqlContent" class="bg-gray-900 text-gray-100 p-6 rounded-lg overflow-auto text-sm"></pre>
            </div>
        </div>
    </div>

    <script>
        // Table expansion
        function toggleTable(tableName) {
            const content = document.getElementById(`content-${tableName}`);
            const icon = document.getElementById(`icon-${tableName}`);
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }

        // Toggle all tables
        function toggleAllTables() {
            const btn = document.getElementById('toggleAllBtn');
            const allExpanded = btn.innerHTML.includes('Collapse');
            
            document.querySelectorAll('[id^="content-"]').forEach(content => {
                if (allExpanded) {
                    content.classList.add('hidden');
                } else {
                    content.classList.remove('hidden');
                }
            });
            
            document.querySelectorAll('[id^="icon-"]').forEach(icon => {
                if (allExpanded) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                } else {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            });
            
            btn.innerHTML = allExpanded 
                ? '<i class="fas fa-chevron-down mr-2"></i> Expand All'
                : '<i class="fas fa-chevron-up mr-2"></i> Collapse All';
        }

        // Table search
        document.getElementById('tableSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.table-card').forEach(card => {
                const tableName = card.getAttribute('data-table-name');
                if (tableName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Show SQL modal
        function showSQL(tableName) {
            // Create a simple SQL statement
            const sql = `CREATE TABLE \`${tableName}\` (\n` +
                        `  id INT PRIMARY KEY AUTO_INCREMENT,\n` +
                        `  -- Table structure goes here\n` +
                        `  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n` +
                        `  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP\n` +
                        `) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;`;
            
            document.getElementById('modalTableName').textContent = `Table: ${tableName}`;
            document.getElementById('sqlContent').textContent = sql;
            document.getElementById('sqlModal').classList.remove('hidden');
        }

        // Preview table data
        function previewData(tableName) {
            window.open(`preview_table.php?table=${tableName}`, '_blank');
        }

        // Close modal
        function closeModal() {
            document.getElementById('sqlModal').classList.add('hidden');
        }

        // Quick actions
        function exportSchema() {
            alert('Export functionality would be implemented here');
        }

        function generateDocumentation() {
            alert('Documentation generation would be implemented here');
        }

        function optimizeDatabase() {
            if (confirm('This will run optimization queries on the database. Continue?')) {
                alert('Database optimization would be implemented here');
            }
        }

        // Close modal on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Print styles
        window.addEventListener('beforeprint', () => {
            document.querySelectorAll('.table-card').forEach(card => {
                const contentId = card.querySelector('[id^="content-"]')?.id;
                if (contentId) {
                    document.getElementById(contentId).classList.remove('hidden');
                }
            });
        });

        <?php if (isset($_GET['table'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            toggleTable('<?= $_GET['table'] ?>');
            document.getElementById('content-<?= $_GET['table'] ?>').scrollIntoView({ behavior: 'smooth' });
        });
        <?php endif; ?>
    </script>
</body>
</html>
<?php $conn->close(); ?>