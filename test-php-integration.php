<?php
/**
 * PHP Integration Test Script
 * Tests database connection and basic functionality
 */

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/functions.php';

// Enable error reporting for testing
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Test results array
$testResults = [];

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>PHP Integration Test - School Management System</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .test { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .pass { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .fail { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .info { background-color: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
        h1, h2 { color: #333; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🧪 PHP Integration Test</h1>
    <p>Testing School Management System PHP backend...</p>";

// Test 1: Database Connection
echo "<h2>📊 Database Connection Test</h2>";
try {
    $database = new Database();
    if ($database->isConnected()) {
        echo "<div class='test pass'>✅ Database connection successful</div>";
        $testResults['database_connection'] = true;
        
        // Test database functionality
        $testQuery = $database->fetch("SELECT 1 as test");
        if ($testQuery && $testQuery['test'] == 1) {
            echo "<div class='test pass'>✅ Database query execution successful</div>";
            $testResults['database_query'] = true;
        } else {
            echo "<div class='test fail'>❌ Database query execution failed</div>";
            $testResults['database_query'] = false;
        }
    } else {
        echo "<div class='test fail'>❌ Database connection failed</div>";
        $testResults['database_connection'] = false;
    }
} catch (Exception $e) {
    echo "<div class='test fail'>❌ Database connection error: " . $e->getMessage() . "</div>";
    $testResults['database_connection'] = false;
}

// Test 2: Session Management
echo "<h2>🔐 Session Management Test</h2>";
try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $sessionManager = new SessionManager($database);
    echo "<div class='test pass'>✅ Session manager initialized</div>";
    $testResults['session_init'] = true;
    
    // Test session functions
    $isLoggedIn = $sessionManager->isLoggedIn();
    echo "<div class='test info'>ℹ️ Current login status: " . ($isLoggedIn ? 'Logged in' : 'Not logged in') . "</div>";
    $testResults['session_status'] = true;
    
} catch (Exception $e) {
    echo "<div class='test fail'>❌ Session management error: " . $e->getMessage() . "</div>";
    $testResults['session_init'] = false;
}

// Test 3: Database Tables
echo "<h2>🗄️ Database Tables Test</h2>";
if ($testResults['database_connection'] ?? false) {
    try {
        // Check if tables exist
        $tables = ['students', 'instructors', 'rooms'];
        foreach ($tables as $table) {
            $exists = $database->exists("SHOW TABLES LIKE '$table'");
            if ($exists) {
                echo "<div class='test pass'>✅ Table '$table' exists</div>";
                $testResults["table_$table"] = true;
                
                // Get record count
                $count = $database->fetch("SELECT COUNT(*) as count FROM $table");
                echo "<div class='test info'>ℹ️ Records in '$table': " . $count['count'] . "</div>";
            } else {
                echo "<div class='test fail'>❌ Table '$table' does not exist</div>";
                $testResults["table_$table"] = false;
            }
        }
    } catch (Exception $e) {
        echo "<div class='test fail'>❌ Table check error: " . $e->getMessage() . "</div>";
    }
} else {
    echo "<div class='test fail'>❌ Cannot test tables - database not connected</div>";
}

// Test 4: API Endpoints
echo "<h2>🔌 API Endpoints Test</h2>";
$apiFiles = [
    '/api/auth.php' => 'Authentication API',
    '/api/rooms.php' => 'Rooms API'
];

foreach ($apiFiles as $file => $description) {
    $filePath = __DIR__ . $file;
    if (file_exists($filePath)) {
        echo "<div class='test pass'>✅ $description file exists</div>";
        $testResults["api_" . basename($file, '.php')] = true;
    } else {
        echo "<div class='test fail'>❌ $description file missing</div>";
        $testResults["api_" . basename($file, '.php')] = false;
    }
}

// Test 5: Helper Functions
echo "<h2>🛠️ Helper Functions Test</h2>";
$functionTests = [
    'validateEmail' => function($test) {
        return validateEmail('test@example.com') && !validateEmail('invalid-email');
    },
    'validateUsername' => function($test) {
        return isValidUsername('validuser123') && !isValidUsername('invalid user!');
    },
    'hashPassword' => function($test) {
        $hash = hashPassword('test123');
        return $hash && strlen($hash) > 50;
    }
];

foreach ($functionTests as $funcName => $testFunc) {
    try {
        $result = $testFunc($this);
        if ($result) {
            echo "<div class='test pass'>✅ Function '$funcName' working correctly</div>";
            $testResults["function_$funcName"] = true;
        } else {
            echo "<div class='test fail'>❌ Function '$funcName' test failed</div>";
            $testResults["function_$funcName"] = false;
        }
    } catch (Exception $e) {
        echo "<div class='test fail'>❌ Function '$funcName' error: " . $e->getMessage() . "</div>";
        $testResults["function_$funcName"] = false;
    }
}

// Test 6: File Structure
echo "<h2>📁 File Structure Test</h2>";
$requiredFiles = [
    '/config/database.php',
    '/includes/session.php',
    '/includes/functions.php',
    '/database/schema.sql',
    '/public/script-php.js',
    '/xampp-setup/setup.md'
];

foreach ($requiredFiles as $file) {
    $filePath = __DIR__ . $file;
    if (file_exists($filePath)) {
        echo "<div class='test pass'>✅ File exists: $file</div>";
        $testResults["file_" . str_replace(['/', '.'], '_', $file)] = true;
    } else {
        echo "<div class='test fail'>❌ File missing: $file</div>";
        $testResults["file_" . str_replace(['/', '.'], '_', $file)] = false;
    }
}

// Test Summary
echo "<h2>📋 Test Summary</h2>";
$totalTests = count($testResults);
$passedTests = count(array_filter($testResults));
$failedTests = $totalTests - $passedTests;

echo "<div class='test info'>ℹ️ Total Tests: $totalTests</div>";
echo "<div class='test pass'>✅ Passed: $passedTests</div>";
echo "<div class='test fail'>❌ Failed: $failedTests</div>";

if ($failedTests === 0) {
    echo "<div class='test pass'>🎉 All tests passed! System is ready for use.</div>";
} else {
    echo "<div class='test fail'>⚠️ Some tests failed. Please check the issues above.</div>";
}

// System Information
echo "<h2>ℹ️ System Information</h2>";
echo "<div class='test info'>";
echo "<strong>PHP Version:</strong> " . phpversion() . "<br>";
echo "<strong>MySQL Support:</strong> " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";
echo "<strong>Session Support:</strong> " . (extension_loaded('session') ? 'Yes' : 'No') . "<br>";
echo "<strong>JSON Support:</strong> " . (extension_loaded('json') ? 'Yes' : 'No') . "<br>";
echo "<strong>Server Time:</strong> " . date('Y-m-d H:i:s') . "<br>";
echo "</div>";

// Next Steps
echo "<h2>🚀 Next Steps</h2>";
echo "<div class='test info'>";
if ($failedTests === 0) {
    echo "✅ System is ready! You can now:<br>";
    echo "1. Access the application at <a href='/'>Home</a><br>";
    echo "2. Test user registration and login<br>";
    echo "3. Test room management features<br>";
    echo "4. Review the documentation in README-PHP.md<br>";
} else {
    echo "⚠️ Please resolve the failed tests before proceeding:<br>";
    echo "1. Check database connection and credentials<br>";
    echo "2. Import the database schema if needed<br>";
    echo "3. Verify all required files are present<br>";
    echo "4. Check XAMPP configuration<br>";
}
echo "</div>";

echo "</body></html>";
?>
