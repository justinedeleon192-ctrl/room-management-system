// Setup and Testing Script for School Management System
const fs = require('fs');
const path = require('path');

console.log('🎓 School Management System - Setup & Test Script');
console.log('='.repeat(50));

// Check if required files exist
const requiredFiles = [
    'server.js',
    'package.json',
    'database.sql',
    '.env',
    'public/index.html',
    'public/login.html',
    'public/register.html',
    'public/student-dashboard.html',
    'public/instructor-dashboard.html'
];

console.log('\n📁 Checking required files...');
let allFilesExist = true;

requiredFiles.forEach(file => {
    const filePath = path.join(__dirname, file);
    if (fs.existsSync(filePath)) {
        console.log(`✅ ${file}`);
    } else {
        console.log(`❌ ${file} - Missing!`);
        allFilesExist = false;
    }
});

if (!allFilesExist) {
    console.log('\n❌ Some required files are missing. Please ensure all files are present.');
    process.exit(1);
}

// Check package.json dependencies
console.log('\n📦 Checking package.json...');
try {
    const packageJson = JSON.parse(fs.readFileSync(path.join(__dirname, 'package.json'), 'utf8'));
    const requiredDependencies = ['express', 'mysql2', 'bcrypt', 'express-session', 'body-parser', 'cors', 'dotenv'];
    
    requiredDependencies.forEach(dep => {
        if (packageJson.dependencies && packageJson.dependencies[dep]) {
            console.log(`✅ ${dep}@${packageJson.dependencies[dep]}`);
        } else {
            console.log(`❌ ${dep} - Missing from dependencies!`);
        }
    });
} catch (error) {
    console.log('❌ Error reading package.json:', error.message);
}

// Check .env file
console.log('\n🔧 Checking environment configuration...');
try {
    const envContent = fs.readFileSync(path.join(__dirname, '.env'), 'utf8');
    const requiredEnvVars = ['DB_HOST', 'DB_USER', 'DB_NAME', 'PORT', 'SESSION_SECRET'];
    
    requiredEnvVars.forEach(envVar => {
        if (envContent.includes(envVar)) {
            console.log(`✅ ${envVar} configured`);
        } else {
            console.log(`❌ ${envVar} - Missing from .env file!`);
        }
    });
} catch (error) {
    console.log('❌ Error reading .env file:', error.message);
}

// Check database schema
console.log('\n🗄️ Checking database schema...');
try {
    const sqlContent = fs.readFileSync(path.join(__dirname, 'database.sql'), 'utf8');
    if (sqlContent.includes('CREATE TABLE students') && sqlContent.includes('CREATE TABLE instructors')) {
        console.log('✅ Database schema contains required tables');
    } else {
        console.log('❌ Database schema missing required tables');
    }
} catch (error) {
    console.log('❌ Error reading database.sql:', error.message);
}

console.log('\n🚀 Setup Instructions:');
console.log('1. Install Node.js and npm from https://nodejs.org/');
console.log('2. Install MySQL and create the database:');
console.log('   mysql -u root -p');
console.log('   CREATE DATABASE school_system;');
console.log('3. Import the database schema:');
console.log('   mysql -u root -p school_system < database.sql');
console.log('4. Install dependencies:');
console.log('   npm install');
console.log('5. Update .env file with your MySQL credentials');
console.log('6. Start the server:');
console.log('   npm start');
console.log('7. Open http://localhost:3000 in your browser');

console.log('\n📋 Testing Checklist:');
console.log('□ Database connection successful');
console.log('□ Registration works for both roles');
console.log('□ Login works for both student and instructor');
console.log('□ Duplicate email/username prevention works');
console.log('□ Password hashing is working');
console.log('□ Session management works');
console.log('□ Role-based redirects work');
console.log('□ Dashboard pages load correctly');

console.log('\n✨ Setup script completed!');
console.log('Follow the instructions above to get your system running.');
