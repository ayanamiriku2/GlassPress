<?php
namespace GlassPress\Core;

/**
 * Media handling - upload, resize, manage.
 * Shared-hosting friendly using GD/Imagick.
 */
class Media
{
    private Database $db;
    private array $config;
    private string $uploadDir;
    private string $uploadUrl;

    private array $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/svg+xml' => 'svg',
        'application/pdf' => 'pdf',
        'application/zip' => 'zip',
        'text/plain' => 'txt',
        'text/csv' => 'csv',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/vnd.ms-excel' => 'xls',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'video/mp4' => 'mp4',
        'audio/mpeg' => 'mp3',
        'audio/wav' => 'wav',
    ];

    private array $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    private array $sizes = [
        'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
        'medium' => ['width' => 300, 'height' => 300, 'crop' => false],
        'large' => ['width' => 1024, 'height' => 1024, 'crop' => false],
    ];

    public function __construct(Database $db, array $config)
    {
        $this->db = $db;
        $this->config = $config;
        $this->uploadDir = GLASSPRESS_ROOT . '/uploads';
        $this->uploadUrl = '/uploads';
    }

    /**
     * Handle file upload from $_FILES.
     */
    public function upload(array $file, int $userId, array $meta = []): ?array
    {
        // Validate file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Check MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!isset($this->allowedTypes[$mimeType])) {
            return null;
        }

        // Check file extension matches MIME
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $expectedExt = $this->allowedTypes[$mimeType];
        
        // Allow jpeg/jpg interchangeability
        $validExts = [$expectedExt];
        if ($expectedExt === 'jpg') $validExts[] = 'jpeg';
        if ($expectedExt === 'jpeg') $validExts[] = 'jpg';
        
        if (!in_array($ext, $validExts)) {
            return null;
        }

        // SVG safety check - disable by default
        if ($mimeType === 'image/svg+xml') {
            return null; // SVG disabled for security
        }

        // Generate path: uploads/YYYY/MM/filename.ext
        $year = date('Y');
        $month = date('m');
        $dir = $this->uploadDir . '/' . $year . '/' . $month;

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Sanitize filename
        $baseName = $this->sanitizeFilename(pathinfo($file['name'], PATHINFO_FILENAME));
        $fileName = $baseName . '.' . $ext;

        // Avoid conflicts
        $counter = 1;
        while (file_exists($dir . '/' . $fileName)) {
            $fileName = $baseName . '-' . $counter . '.' . $ext;
            $counter++;
        }

        $filePath = $dir . '/' . $fileName;
        $relativeUrl = $this->uploadUrl . '/' . $year . '/' . $month . '/' . $fileName;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return null;
        }

        chmod($filePath, 0644);

        // Get image dimensions if applicable
        $width = 0;
        $height = 0;
        $sizes = [];

        if (in_array($mimeType, $this->imageTypes)) {
            $imageInfo = getimagesize($filePath);
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }

            // Generate size variants
            $sizes = $this->generateSizes($filePath, $mimeType, $baseName, $ext, $dir, $year, $month);
        }

        // Store in database
        $mediaId = $this->db->insert('media', [
            'user_id' => $userId,
            'filename' => $fileName,
            'original_name' => $file['name'],
            'mime_type' => $mimeType,
            'file_size' => filesize($filePath),
            'file_path' => $relativeUrl,
            'width' => $width,
            'height' => $height,
            'alt_text' => $meta['alt_text'] ?? '',
            'caption' => $meta['caption'] ?? '',
            'description' => $meta['description'] ?? '',
            'sizes' => json_encode($sizes),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return [
            'id' => $mediaId,
            'url' => $relativeUrl,
            'filename' => $fileName,
            'mime_type' => $mimeType,
            'sizes' => $sizes,
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * Generate image size variants.
     */
    private function generateSizes(string $sourcePath, string $mimeType, string $baseName, string $ext, string $dir, string $year, string $month): array
    {
        $sizes = [];
        $sourceImage = $this->loadImage($sourcePath, $mimeType);
        
        if (!$sourceImage) {
            return $sizes;
        }

        $srcWidth = imagesx($sourceImage);
        $srcHeight = imagesy($sourceImage);

        foreach ($this->sizes as $sizeName => $sizeConfig) {
            $targetWidth = $sizeConfig['width'];
            $targetHeight = $sizeConfig['height'];
            $crop = $sizeConfig['crop'];

            // Skip if source is smaller
            if ($srcWidth <= $targetWidth && $srcHeight <= $targetHeight) {
                continue;
            }

            if ($crop) {
                $ratio = max($targetWidth / $srcWidth, $targetHeight / $srcHeight);
                $newWidth = (int) ($targetWidth);
                $newHeight = (int) ($targetHeight);
                $srcX = (int) (($srcWidth - $targetWidth / $ratio) / 2);
                $srcY = (int) (($srcHeight - $targetHeight / $ratio) / 2);
                $srcCropWidth = (int) ($targetWidth / $ratio);
                $srcCropHeight = (int) ($targetHeight / $ratio);
            } else {
                $ratio = min($targetWidth / $srcWidth, $targetHeight / $srcHeight);
                $newWidth = (int) ($srcWidth * $ratio);
                $newHeight = (int) ($srcHeight * $ratio);
                $srcX = 0;
                $srcY = 0;
                $srcCropWidth = $srcWidth;
                $srcCropHeight = $srcHeight;
            }

            $resized = imagecreatetruecolor($newWidth, $newHeight);

            // Preserve transparency for PNG/WebP
            if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefill($resized, 0, 0, $transparent);
            }

            imagecopyresampled($resized, $sourceImage, 0, 0, $srcX, $srcY, $newWidth, $newHeight, $srcCropWidth, $srcCropHeight);

            $sizeFileName = $baseName . '-' . $newWidth . 'x' . $newHeight . '.' . $ext;
            $sizeFilePath = $dir . '/' . $sizeFileName;

            $this->saveImage($resized, $sizeFilePath, $mimeType);
            imagedestroy($resized);

            $sizes[$sizeName] = [
                'url' => $this->uploadUrl . '/' . $year . '/' . $month . '/' . $sizeFileName,
                'width' => $newWidth,
                'height' => $newHeight,
                'file' => $sizeFileName,
            ];
        }

        imagedestroy($sourceImage);
        return $sizes;
    }

    private function loadImage(string $path, string $mimeType): ?\GdImage
    {
        return match ($mimeType) {
            'image/jpeg' => @imagecreatefromjpeg($path) ?: null,
            'image/png' => @imagecreatefrompng($path) ?: null,
            'image/gif' => @imagecreatefromgif($path) ?: null,
            'image/webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            default => null,
        };
    }

    private function saveImage(\GdImage $image, string $path, string $mimeType): bool
    {
        return match ($mimeType) {
            'image/jpeg' => imagejpeg($image, $path, 85),
            'image/png' => imagepng($image, $path, 8),
            'image/gif' => imagegif($image, $path),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, $path, 85) : false,
            default => false,
        };
    }

    private function sanitizeFilename(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);
        $name = trim($name, '-');
        return strtolower($name) ?: 'file';
    }

    /**
     * Get a media item by ID.
     */
    public function get(int $id): ?array
    {
        return $this->db->fetch(
            sprintf('SELECT * FROM %s WHERE id = ?', $this->db->prefix('media')),
            [$id]
        );
    }

    /**
     * List media with pagination.
     */
    public function list(int $page = 1, int $perPage = 24, string $type = '', string $search = ''): array
    {
        $where = '1=1';
        $params = [];

        if ($type) {
            $where .= ' AND mime_type LIKE ?';
            $params[] = $type . '%';
        }

        if ($search) {
            $where .= ' AND (filename LIKE ? OR original_name LIKE ? OR alt_text LIKE ? OR caption LIKE ?)';
            $searchParam = '%' . $search . '%';
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
        }

        $total = $this->db->count('media', $where, $params);
        $offset = ($page - 1) * $perPage;

        $items = $this->db->fetchAll(
            sprintf(
                'SELECT * FROM %s WHERE %s ORDER BY created_at DESC LIMIT %d OFFSET %d',
                $this->db->prefix('media'),
                $where,
                $perPage,
                $offset
            ),
            $params
        );

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pages' => ceil($total / $perPage),
        ];
    }

    /**
     * Delete a media item and its files.
     */
    public function deleteMedia(int $id): bool
    {
        $media = $this->get($id);
        if (!$media) {
            return false;
        }

        // Delete physical files
        $mainFile = GLASSPRESS_ROOT . $media['file_path'];
        if (file_exists($mainFile)) {
            @unlink($mainFile);
        }

        // Delete size variants
        $sizes = json_decode($media['sizes'] ?? '{}', true);
        if (is_array($sizes)) {
            $dir = dirname($mainFile);
            foreach ($sizes as $size) {
                $sizeFile = $dir . '/' . ($size['file'] ?? '');
                if (file_exists($sizeFile)) {
                    @unlink($sizeFile);
                }
            }
        }

        $this->db->delete('media', 'id = ?', [$id]);
        return true;
    }

    /**
     * Update media metadata.
     */
    public function updateMeta(int $id, array $data): bool
    {
        $allowed = ['alt_text', 'caption', 'description'];
        $update = array_intersect_key($data, array_flip($allowed));
        $update['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->update('media', $update, 'id = ?', [$id]) > 0;
    }

    /**
     * Get URL for a specific size.
     */
    public function getUrl(array $media, string $size = 'full'): string
    {
        if ($size === 'full') {
            return $media['file_path'];
        }

        $sizes = json_decode($media['sizes'] ?? '{}', true);
        return $sizes[$size]['url'] ?? $media['file_path'];
    }
}
