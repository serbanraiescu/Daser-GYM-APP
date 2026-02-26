const fs = require('fs');
const path = require('path');
const archiver = require('archiver');
const axios = require('axios');
const FormData = require('form-data');

// --- CONFIGURATION ---
const DOMAIN = 'firstgym.ro';
const UPLOAD_URL = `https://${DOMAIN}/quick_update.php`;
const SECRET_TOKEN = 'daser_gym_secure_2025';
const OUTPUT_FILE = 'update.zip';

// Folders to INCLUDE
const INCLUDES_DIRS = [
    'app',
    'config',
    'database',
    'resources',
    'routes',
    // We EXCLUDE storage for now to avoid conflicts and speed up
];

const INCLUDES_FILES = [
    'bootstrap/app.php',
    '.env.example',
    '.htaccess', // This should be the root-optimized one
    'composer.json',
    'artisan',
    'quick_update.php'
];

async function zipChanges(outPath) {
    const archive = archiver('zip', { zlib: { level: 9 } });
    const stream = fs.createWriteStream(outPath);

    return new Promise((resolve, reject) => {
        archive.on('error', err => reject(err));
        archive.pipe(stream);

        // Add regular directories
        for (const dir of INCLUDES_DIRS) {
            if (fs.existsSync(dir)) {
                archive.directory(dir, dir);
            }
        }

        // Add regular files
        for (const file of INCLUDES_FILES) {
            if (fs.existsSync(file)) {
                archive.file(file, { name: file });
            }
        }

        // CRITICAL: Flatten public/ folder (excluding index.php)
        if (fs.existsSync('public')) {
            console.log('Adding public assets (excluding index.php)...');
            archive.directory('public/', false, (entry) => {
                if (entry.name === 'index.php') return false;
                if (entry.name === 'storage') return false; // Skip the symlink
                return entry;
            });
        }

        // Add the Root-Corrected index.php (index-root.php)
        if (fs.existsSync('index-root.php')) {
            console.log('Adding root-corrected index.php...');
            archive.file('index-root.php', { name: 'index.php' });
        }

        // Re-enabled .htaccess now that index.php is fixed
        if (fs.existsSync('SAFE_.htaccess')) {
            console.log('Adding SAFE .htaccess...');
            archive.file('SAFE_.htaccess', { name: '.htaccess' });
        }

        // Also add the .env if it exists
        if (fs.existsSync('.env')) {
            archive.file('.env', { name: '.env' });
        }

        stream.on('close', () => resolve());
        archive.finalize();
    });
}

async function uploadFile(filePath) {
    console.log(`Uploading ${filePath} to ${UPLOAD_URL}...`);

    const form = new FormData();
    form.append('token', SECRET_TOKEN);
    form.append('update_zip', fs.createReadStream(filePath));

    try {
        const response = await axios.post(UPLOAD_URL, form, {
            headers: form.getHeaders(),
            maxContentLength: Infinity,
            maxBodyLength: Infinity
        });
        console.log('Server Response:', response.data);
    } catch (error) {
        if (error.response) {
            console.error('Upload Failed with status:', error.response.status);
            console.error('Server Data:', error.response.data);
        } else {
            console.error('Upload Failed Error:', error.message);
        }
    }
}

async function run() {
    console.log('--- Daser Quick Deploy (Optimized) ---');

    try {
        console.log('Creating update package...');
        await zipChanges(OUTPUT_FILE);

        await uploadFile(OUTPUT_FILE);

        console.log('--- Deploy Complete! ---');
    } catch (err) {
        console.error('Deployment Error:', err);
    }
}

run();
