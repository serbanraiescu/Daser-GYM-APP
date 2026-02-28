<?php
/**
 * Daser Gym App - Create Build Symlink
 * This script creates a symlink from the true repository public/build folder
 * to the public_html root, similar to storage:link.
 */

$targetFolder = __DIR__ . '/daser_gym_app/public/build';
$linkFolder = __DIR__ . '/build';

echo "<h1>Generare Symlink pentru fisierele Build (Vite)</h1>";

if (!file_exists($targetFolder)) {
    echo "<p style='color:red;'>Eroare: Folderul sursa nu exista ($targetFolder). Asigura-te ca ai dat Update din cPanel.</p>";
    exit;
}

if (file_exists($linkFolder)) {
    if (is_link($linkFolder)) {
        echo "<p style='color:green;'>Symlink-ul exista deja.</p>";
        exit;
    } else {
        // It's a real directory, probably copied by cp -R. Let's remove it and link it.
        echo "<p style='color:orange;'>Exista un folder fizic 'build'. Il stergem pentru a crea symlink-ul...</p>";
        // Recursive delete
        function deleteDir($dirPath) {
            if (!is_dir($dirPath)) {
                throw new InvalidArgumentException("$dirPath must be a directory");
            }
            if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
                $dirPath .= '/';
            }
            $files = glob($dirPath . '*', GLOB_MARK);
            foreach ($files as $file) {
                if (is_dir($file)) {
                    deleteDir($file);
                } else {
                    unlink($file);
                }
            }
            rmdir($dirPath);
        }
        try {
            deleteDir($linkFolder);
        } catch (Exception $e) {
            echo "<p style='color:red;'>Nu am putut sterge folderul vechi: " . $e->getMessage() . "</p>";
            exit;
        }
    }
}

// Create the symlink
$success = symlink($targetFolder, $linkFolder);

if ($success) {
    echo "<p style='color:green; font-weight:bold;'>Succes! Folderul 'build' a fost legat (symlink) corect catre 'daser_gym_app/public/build'.</p>";
    echo "<p>Poti sterge acest fisier (create_symlinks.php) acum pentru securitate.</p>";
} else {
    echo "<p style='color:red;'>Eroare: Nu am putut crea symlink-ul. Verifica permisiunile din cPanel.</p>";
}
