const fs = require('fs');
const path = require('path');

// Vercel Build Script - Replaces environment variables in static files
console.log('🔧 Injecting environment variables...');

const configPath = path.join(__dirname, 'ayamkings_frontend', 'config.js');

if (fs.existsSync(configPath)) {
    let content = fs.readFileSync(configPath, 'utf8');

    // Replace __BACKEND_URL__ with actual environment variable
    const backendUrl = process.env.BACKEND_URL || 'https://ayamkings-production.up.railway.app';
    content = content.replace(/__BACKEND_URL__/g, backendUrl);

    fs.writeFileSync(configPath, content);
    console.log(`✅ Backend URL set to: ${backendUrl}`);
} else {
    console.log('⚠️ config.js not found');
}

console.log('✅ Build complete!');
