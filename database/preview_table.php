<?php
// preview_table.php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'newsnest_hub';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$table = isset($_GET['table']) ? $conn->real_escape_string($_GET['table']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: <?= $table ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --dark-green: #134E5E;
            --light-green: #71B280;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-[#134E5E] mb-6">
            <i class="fas fa-table mr-2"></i>
            Preview: <?= $table ?>
        </h1>
        
        <?php
        if ($table) {
            $result = $conn->query("SELECT * FROM $table LIMIT 50");
            if ($result && $result->num_rows > 0) {
                echo '<div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">';
                echo '<table class="min-w-full divide-y divide-gray-200">';
                
                // Table header
                echo '<thead class="bg-gradient-to-r from-[#134E5E] to-[#71B280] text-white">';
                echo '<tr>';
                while ($field = $result->fetch_field()) {
                    echo '<th class="px-6 py-3 text-left text-xs font-semibold uppercase">' . $field->name . '</th>';
                }
                echo '</tr>';
                echo '</thead>';
                
                // Table body
                echo '<tbody class="divide-y divide-gray-200">';
                while ($row = $result->fetch_assoc()) {
                    echo '<tr class="hover:bg-gray-50">';
                    foreach ($row as $value) {
                        echo '<td class="px-6 py-4 text-sm text-gray-800 border">';
                        echo htmlspecialchars($value ?? 'NULL');
                        echo '</td>';
                    }
                    echo '</tr>';
                }
                echo '</tbody>';
                echo '</table>';
                echo '</div>';
            } else {
                echo '<div class="text-center py-12 bg-white rounded-lg">';
                echo '<i class="fas fa-database text-5xl text-gray-300 mb-4"></i>';
                echo '<p class="text-gray-600">No data found in table: ' . $table . '</p>';
                echo '</div>';
            }
        }
        ?>
    </div>
</body>
</html>
<?php $conn->close(); ?>