<?php
namespace GlassPress\App\Admin\Controllers;

class MediaController extends AdminController
{
    public function index(): void
    {
        $db = $this->app->getService('db');
        $search = trim($_GET['s'] ?? '');
        $type = $_GET['type'] ?? '';
        $page = max(1, (int) ($_GET['paged'] ?? 1));
        $perPage = 24;

        $where = '1=1';
        $params = [];

        if ($search) {
            $where .= ' AND (m.filename LIKE ? OR m.alt_text LIKE ? OR m.original_name LIKE ?)';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
            $params[] = '%' . $search . '%';
        }

        if ($type && in_array($type, ['image', 'document', 'video', 'audio'])) {
            $typeMap = [
                'image' => "m.mime_type LIKE 'image/%'",
                'document' => "(m.mime_type LIKE 'application/%' OR m.mime_type LIKE 'text/%')",
                'video' => "m.mime_type LIKE 'video/%'",
                'audio' => "m.mime_type LIKE 'audio/%'",
            ];
            $where .= ' AND ' . $typeMap[$type];
        }

        $total = (int) $db->fetchColumn(
            sprintf('SELECT COUNT(*) FROM %s m WHERE %s', $db->prefix('media'), $where),
            $params
        );

        $media = $db->fetchAll(sprintf(
            "SELECT m.*, u.display_name as uploader_name FROM %s m 
             LEFT JOIN %s u ON m.user_id = u.id 
             WHERE %s ORDER BY m.created_at DESC LIMIT %d OFFSET %d",
            $db->prefix('media'), $db->prefix('users'), $where, $perPage, ($page - 1) * $perPage
        ), $params);

        $this->render('media.index', [
            'pageTitle' => 'Media Library',
            'media' => $media,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pages' => ceil($total / $perPage),
            'search' => $search,
            'type' => $type,
        ]);
    }

    public function create(): void
    {
        $this->requireCapability('upload_files');
        $this->render('media.upload', [
            'pageTitle' => 'Upload Media',
        ]);
    }

    public function store(): void
    {
        $this->requireCapability('upload_files');
        $mediaService = $this->app->getService('media');

        if (empty($_FILES['files'])) {
            $this->redirect($this->app->getAdminUrl('media/create'), 'No files selected.', 'error');
            return;
        }

        $files = $_FILES['files'];
        $uploaded = 0;
        $errors = [];

        // Normalize single/multiple file arrays
        if (!is_array($files['name'])) {
            $files = [
                'name' => [$files['name']],
                'type' => [$files['type']],
                'tmp_name' => [$files['tmp_name']],
                'error' => [$files['error']],
                'size' => [$files['size']],
            ];
        }

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $errors[] = $files['name'][$i] . ': Upload error';
                continue;
            }

            $singleFile = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];

            $result = $mediaService->upload($singleFile, $this->app->getService('auth')->userId());

            if ($result) {
                $uploaded++;
            } else {
                $errors[] = $files['name'][$i] . ': Failed to upload';
            }
        }

        $msg = $uploaded . ' file(s) uploaded successfully.';
        if ($errors) {
            $msg .= ' Errors: ' . implode(', ', $errors);
        }

        $this->redirect($this->app->getAdminUrl('media'), $msg, $errors ? 'warning' : 'success');
    }

    public function edit(string $id): void
    {
        $db = $this->app->getService('db');
        $mediaId = (int) $id;

        $item = $db->fetch(
            sprintf('SELECT m.*, u.display_name as uploader_name FROM %s m LEFT JOIN %s u ON m.user_id = u.id WHERE m.id = ?', $db->prefix('media'), $db->prefix('users')),
            [$mediaId]
        );

        if (!$item) {
            $this->redirect($this->app->getAdminUrl('media'), 'Media not found.', 'error');
            return;
        }

        $this->render('media.edit', [
            'pageTitle' => 'Edit Media',
            'item' => $item,
        ]);
    }

    public function update(string $id): void
    {
        $db = $this->app->getService('db');
        $mediaId = (int) $id;

        $db->update('media', [
            'original_name' => trim($_POST['title'] ?? ''),
            'alt_text' => trim($_POST['alt_text'] ?? ''),
            'caption' => trim($_POST['caption'] ?? ''),
        ], 'id = ?', [$mediaId]);

        $this->redirect($this->app->getAdminUrl("media/edit/{$mediaId}"), 'Media updated.');
    }

    public function delete(string $id): void
    {
        $db = $this->app->getService('db');
        $mediaId = (int) $id;

        $item = $db->fetch(
            sprintf('SELECT * FROM %s WHERE id = ?', $db->prefix('media')),
            [$mediaId]
        );

        if ($item) {
            // Delete physical files
            $basePath = GLASSPRESS_ROOT . $item['file_path'];
            if (file_exists($basePath)) {
                @unlink($basePath);
            }
            // Delete thumbnails from sizes metadata
            if (!empty($item['sizes'])) {
                $sizes = json_decode($item['sizes'], true);
                if (is_array($sizes)) {
                    foreach ($sizes as $sizeData) {
                        if (!empty($sizeData['url'])) {
                            $sizePath = GLASSPRESS_ROOT . $sizeData['url'];
                            if (file_exists($sizePath)) {
                                @unlink($sizePath);
                            }
                        }
                    }
                }
            }

            // Remove from posts that use it as featured image
            $db->query(
                sprintf("UPDATE %s SET featured_image_id = NULL WHERE featured_image_id = ?", $db->prefix('posts')),
                [$mediaId]
            );

            $db->delete('media', 'id = ?', [$mediaId]);
        }

        $this->redirect($this->app->getAdminUrl('media'), 'Media deleted.');
    }

    // AJAX: Upload via API
    public function apiUpload(): void
    {
        $this->requireCapability('upload_files');
        $mediaService = $this->app->getService('media');

        if (empty($_FILES['file'])) {
            $this->json(['success' => false, 'error' => 'No file provided'], 400);
            return;
        }

        $result = $mediaService->upload($_FILES['file'], $this->app->getService('auth')->userId());

        if ($result) {
            $this->json(['success' => true, 'media' => $result]);
        } else {
            $this->json(['success' => false, 'error' => 'Upload failed'], 500);
        }
    }

    // AJAX: List media for browse modal
    public function apiList(): void
    {
        $db = $this->app->getService('db');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 20;

        $media = $db->fetchAll(sprintf(
            "SELECT * FROM %s WHERE mime_type LIKE 'image/%%' ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $db->prefix('media'), $perPage, ($page - 1) * $perPage
        ));

        $this->json(['success' => true, 'media' => $media]);
    }
}
