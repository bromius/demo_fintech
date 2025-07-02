<?php
/**
 * File management handler
 */
class File
{
    /**
     * @var string Base directory for file operations
     */
    private $baseDir;

    /**
     * Initialize with base directory
     * 
     * @param string|null $baseDir Custom base directory (uses config if null)
     */
    public function __construct($baseDir = null)
    {
        $this->baseDir = $baseDir ?: cfg('path.upload');
        static::ensureDirectoryExists($this->baseDir);
    }

    /**
     * Save uploaded file
     * 
     * @param array|string $file File data from $_FILES 
     * @param string|null $destination Relative path to save (optional)
     * @return string Full path to saved file
     * @throws Exception On failure
     */
    public function upload($file, $destination = null)
    {
        if (!isset($file['tmp_name'])) {
            throw new Exception('Invalid file data - missing tmp_name');
        }

        $sourcePath = $file['tmp_name'];
        $originalName = isset($file['name']) ? $file['name'] : basename($sourcePath);
        $destination = $destination ?: $originalName;

        $fullPath = $this->getFullPath($destination);
        static::ensureDirectoryExists(dirname($fullPath));

        if (is_array($file)) {
            if (!move_uploaded_file($sourcePath, $fullPath)) {
                throw new Exception('Failed to save uploaded file');
            }
        } else {
            if (!rename($sourcePath, $fullPath)) {
                throw new Exception('Failed to move file');
            }
        }

        return $fullPath;
    }

    /**
     * Save file contents
     * 
     * @param string $content File content
     * @param string $destination Relative path to save
     * @return string Full path to saved file
     * @throws Exception On failure
     */
    public function saveContent($content, $destination)
    {
        $fullPath = $this->getFullPath($destination);
        static::ensureDirectoryExists(dirname($fullPath));

        if (file_put_contents($fullPath, $content) === false) {
            throw new Exception('Failed to save file content');
        }

        return $fullPath;
    }

    /**
     * Delete file
     * 
     * @param string $path Relative file path
     * @return bool True on success
     */
    public function delete($path)
    {
        $fullPath = $this->getFullPath($path);
        return file_exists($fullPath) ? unlink($fullPath) : false;
    }

    /**
     * Get file contents
     * 
     * @param string $path Relative file path
     * @return string File content
     * @throws Exception If file doesn't exist
     */
    public function getContent($path)
    {
        $fullPath = $this->getFullPath($path);
        if (!file_exists($fullPath)) {
            throw new Exception('File not found');
        }
        return file_get_contents($fullPath);
    }

    /**
     * Check if file exists
     * 
     * @param string $path Relative file path
     * @return bool
     */
    public function exists($path)
    {
        return file_exists($this->getFullPath($path));
    }

    /**
     * Get file size in bytes
     * 
     * @param string $path Relative file path
     * @return int
     * @throws Exception If file doesn't exist
     */
    public function size($path)
    {
        $fullPath = $this->getFullPath($path);
        if (!file_exists($fullPath)) {
            throw new Exception('File not found');
        }
        return filesize($fullPath);
    }

    /**
     * Get file modification time
     * 
     * @param string $path Relative file path
     * @return int Unix timestamp
     * @throws Exception If file doesn't exist
     */
    public function modifiedTime($path)
    {
        $fullPath = $this->getFullPath($path);
        if (!file_exists($fullPath)) {
            throw new Exception('File not found');
        }
        return filemtime($fullPath);
    }

    /**
     * Normalizes path (removes ./.. also if path does doesn't exist)
     * 
     * @param string $path Path
     * @return string Normalized path
     */
    public static function normalizePath($path) {
        $parts = [];
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $segments = explode(DIRECTORY_SEPARATOR, $path);

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($parts);
            } else {
                $parts[] = $segment;
            }
        }

        if (preg_match('/^[a-zA-Z]:$/', $segments[0])) {
            // Windows drive normalization
            $normalized = array_shift($parts) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $parts);
        } else {
            $normalized = implode(DIRECTORY_SEPARATOR, $parts);
        }

        return $normalized;
    }

    /**
     * Get absolute path from relative
     * 
     * @param string $path Relative path
     * @return string Absolute path
     */
    public function getFullPath($path)
    {
        return static::normalizePath(rtrim($this->baseDir, '/') . '/' . ltrim($path, '/'));
    }

    /**
     * Ensure directory exists
     * 
     * @param string $dir Directory path
     * @throws Exception On failure
     */
    public static function ensureDirectoryExists($dir)
    {
        if (!file_exists($dir) && !mkdir($dir, 0755, true)) {
            throw new Exception("Failed to create directory: {$dir}");
        }
    }
}