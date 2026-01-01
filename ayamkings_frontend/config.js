// ==========================================
// AyamKings Configuration
// ==========================================
// This file contains dynamic URL configuration
// All other files will reference this config

// ==========================================
// Environment Detection
// ==========================================
const isVercel = window.location.hostname.includes('vercel.app') || window.location.hostname.includes('your-domain.com');
const isLocalDev = window.location.port === '5500' || window.location.port === '8080';
const isXAMPP = window.location.port === '80' || window.location.pathname.includes('Coding%20PSM');

// ==========================================
// 🔴 IMPORTANT: Update this URL after deploying backend
// ==========================================
const PRODUCTION_BACKEND_URL = 'https://YOUR_BACKEND_URL_HERE'; // ← Update ini bila backend dah deploy

const CONFIG = {
    // API Backend URL - auto-detect environment
    API_BASE_URL: isVercel
        ? PRODUCTION_BACKEND_URL  // Production: Vercel frontend → InfinityFree/Railway backend
        : isLocalDev
            ? 'http://localhost:8000'  // Development: frontend on 5500, backend on 8000
            : window.location.origin + '/Coding%20PSM/ayamkings_backend', // XAMPP fallback

    // Frontend Base URL - auto-detect from current location
    FRONTEND_BASE_URL: isVercel
        ? window.location.origin  // Vercel: root URL
        : isLocalDev
            ? window.location.origin  // Local dev: root URL
            : window.location.origin + '/Coding%20PSM/ayamkings_frontend', // XAMPP

    // Uploads folder URL - for production, images should be in backend
    UPLOADS_URL: isVercel
        ? PRODUCTION_BACKEND_URL + '/uploads'  // Production: uploads from backend
        : isLocalDev
            ? 'http://localhost:5500/uploads'
            : window.location.origin + '/Coding%20PSM/ayamkings_frontend/uploads'
};

// Helper function to get API endpoint
function getApiUrl(endpoint) {
    return `${CONFIG.API_BASE_URL}/${endpoint}`;
}

// Helper function to get frontend page URL
function getPageUrl(page) {
    return `${CONFIG.FRONTEND_BASE_URL}/${page}`;
}

// Helper function to get upload image URL
function getUploadUrl(imageUrl) {
    // No image - return placeholder
    if (!imageUrl || imageUrl.trim() === '') {
        return 'https://placehold.co/100x100/FFD700/8B4513?text=Item';
    }

    // If it's already a placeholder URL, return as-is
    if (imageUrl.includes('placehold.co')) {
        return imageUrl;
    }

    // If it's an old localhost URL (with or without Coding PSM), extract filename
    if (imageUrl.includes('localhost') || imageUrl.includes('127.0.0.1')) {
        const parts = imageUrl.split('/');
        const filename = parts[parts.length - 1];
        // Return with correct path for current environment
        return `${CONFIG.UPLOADS_URL}/${filename}`;
    }

    // If it's just a filename or relative path
    if (!imageUrl.startsWith('http')) {
        // Handle cases like "uploads/filename.jpg" or just "filename.jpg"
        const filename = imageUrl.includes('/') ? imageUrl.split('/').pop() : imageUrl;
        return `${CONFIG.UPLOADS_URL}/${filename}`;
    }

    // For any other full URL, return as-is
    return imageUrl;
}


// ==========================================
// Session Management (Auto-Logout after 10 mins)
// ==========================================
const SESSION_TIMEOUT_MS = 1 * 60 * 60 * 1000; // 1 Hour

// Initialize or Reset Session Timer
function startSession() {
    localStorage.setItem('lastActivity', Date.now());
    console.log("Session started/refreshed at: " + new Date().toLocaleTimeString());
}

// Check if Session is Expired
function checkSession() {
    const token = localStorage.getItem('userToken');
    if (!token) return; // No active session to check

    const lastActivity = localStorage.getItem('lastActivity');
    if (lastActivity) {
        const now = Date.now();
        const timeElapsed = now - parseInt(lastActivity);

        if (timeElapsed > SESSION_TIMEOUT_MS) {
            console.log("Session expired. Logging out...");
            // Session Expired
            localStorage.clear();
            const currentPage = window.location.pathname.split('/').pop();

            // Avoid redirect loops if already on login pages, though clear() handles it
            if (!currentPage.includes('login') && !currentPage.includes('index') && !currentPage.includes('register')) {
                alert("Your session has expired due to inactivity (10 mins). Please log in again.");
                window.location.href = 'index.html';
            } else {
                // Even if on public page, refresh to update UI state (e.g., remove 'Logout' button)
                window.location.reload();
            }
        } else {
            // Valid session - verify if we need to set lastActivity for first time
        }
    } else {
        // Token exists but no activity record? Start now.
        startSession();
    }
}

// Global Activity Listener to Reset Timer
function resetSessionTimer() {
    if (localStorage.getItem('userToken')) {
        startSession(); // Update timestamp
    }
}

// Attach listeners for user activity
window.addEventListener('mousemove', resetSessionTimer);
window.addEventListener('keydown', resetSessionTimer);
window.addEventListener('click', resetSessionTimer);
window.addEventListener('scroll', resetSessionTimer);

// Check session immediately on load
checkSession();

// Check periodically every 1 minute
setInterval(checkSession, 60 * 1000);

// Export session functions
window.startSession = startSession;
window.checkSession = checkSession;
