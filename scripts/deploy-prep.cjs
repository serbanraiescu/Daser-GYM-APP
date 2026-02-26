const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

// Configuration
const OUTPUT_FILE = 'full_deploy.zip';

async function zipDirectory(sourceDir, outPath) {
    const archive = archiver('zip', { zlib: { level: 9 } });
    const stream = fs.createWriteStream(outPath);

    return new Promise((resolve, reject) => {
        stream.on('close', () => resolve());
        archive.on('error', err => reject(err));
        archive.pipe(stream);

        // Step 1: Add directories at their root level (excluding public and vendor)
        const dirs = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'storage'];
        for (const dir of dirs) {
            const fullPath = path.join(sourceDir, dir);
            if (fs.existsSync(fullPath)) {
                archive.directory(fullPath, dir);
            }
        }

        // Step 2: Add Vendor (Important for cPanel)
        const vendorPath = path.join(sourceDir, 'vendor');
        if (fs.existsSync(vendorPath)) {
            console.log('Adding vendor (this might take a while)...');
            archive.directory(vendorPath, 'vendor');
        }

        // Step 3: Add core files from the root
        const rootFiles = ['artisan', 'composer.json', 'composer.lock', 'database_dump_for_mysql.sql', '.env.example', 'quick_update.php'];
        for (const file of rootFiles) {
            const filePath = path.join(sourceDir, file);
            if (fs.existsSync(filePath)) {
                archive.file(filePath, { name: file });
            }
        }

        // Step 4: CRITICAL - Flatten the public folder
        // Everything inside public/ goes to the root of the ZIP
        const publicPath = path.join(sourceDir, 'public');
        if (fs.existsSync(publicPath)) {
            console.log('Flattening public/ folder into root...');
            archive.directory(publicPath, false, (entry) => {
                if (entry.name === 'index.php') return false; // Skip the standard index.php
                return entry;
            });
        }

        // Add the Root-Corrected index.php
        const indexRootPath = path.join(sourceDir, 'index-root.php');
        if (fs.existsSync(indexRootPath)) {
            archive.file(indexRootPath, { name: 'index.php' });
        }

        // Add the SAFE .htaccess
        const htaccessSafePath = path.join(sourceDir, 'SAFE_.htaccess');
        if (fs.existsSync(htaccessSafePath)) {
            archive.file(htaccessSafePath, { name: '.htaccess' });
        }

        // Handle quick_update.php if it was in the root or in public/
        // We already added it from root in Step 3 if it was there.

        // Also add the .env file if it exists
        const envPath = path.join(sourceDir, '.env');
        if (fs.existsSync(envPath)) {
            archive.file(envPath, { name: '.env' });
        }

        archive.finalize();
    });
}

async function run() {
    console.log('--- Starting Deployment Prep ---');

    const projectRoot = process.cwd();
    console.log(`Project Root: ${projectRoot}`);

    if (fs.existsSync(OUTPUT_FILE)) {
        console.log(`Removing old ${OUTPUT_FILE}...`);
        fs.unlinkSync(OUTPUT_FILE);
    }

    console.log('Zipping files (including vendor)... this may take a minute...');
    try {
        await zipDirectory(projectRoot, OUTPUT_FILE);
        const stats = fs.statSync(OUTPUT_FILE);
        const fileSizeInMegabytes = stats.size / (1024 * 1024);
        console.log(`--- Deployment Prep Complete! ---`);
        console.log(`File: ${OUTPUT_FILE}`);
        console.log(`Size: ${fileSizeInMegabytes.toFixed(2)} MB`);
        console.log(`\nNext Steps:`);
        console.log(`1. Upload ${OUTPUT_FILE} to cPanel public_html`);
        console.log(`2. Extract and access your domain.`);
    } catch (err) {
        console.error('Error during zipping:', err);
    }
}

run();
