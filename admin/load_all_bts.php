<?php
declare(strict_types=1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Bootstrap the application (loads autoloader, Eloquent, middleware)
require_once 'inc/middleware_loader.php';

use App\Models\BehindTheScene;

// --- Security First: CSRF Token Validation ---
$csrf_token_from_request = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token_from_request)) {
    http_response_code(403); // Forbidden
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid security token. Please refresh the page and try again.']);
    exit;
}

// --- Input Sanitization and Pagination ---
$page = filter_input(INPUT_POST, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
$limit = filter_input(INPUT_POST, 'limit', FILTER_VALIDATE_INT, ['options' => ['default' => 10, 'min_range' => 1]]);
$search_term = trim($_POST['search'] ?? '');

// --- Eloquent Query ---
$query = BehindTheScene::query()
    ->select('behind_the_scenes.*', 'seasons.title as season_title')
    ->leftJoin('seasons', 'behind_the_scenes.season', '=', 'seasons.id');

if (!empty($search_term)) {
    $query->where(function ($q) use ($search_term) {
        $searchTermWildcard = "%{$search_term}%";
        $q->where('behind_the_scenes.title', 'LIKE', $searchTermWildcard)
          ->orWhere('seasons.title', 'LIKE', $searchTermWildcard);
    });
}

// Order and Paginate
$paginator = $query->orderBy('behind_the_scenes.short_order', 'asc')
                   ->orderBy('behind_the_scenes.create_date', 'desc')
                   ->paginate($limit, ['*'], 'page', $page);

$csrf_token = $_SESSION['csrf_token'];
?>
<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Title</th>
                <th>Season</th>
                <th>Day</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($paginator->count() > 0): ?>
                <?php foreach ($paginator as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars((string) ($row->short_order ?? '')) ?></td>
                        <td><?= htmlspecialchars($row->title ?? '') ?></td>
                        <td><?= htmlspecialchars($row->season_title ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($row->day ?? '') ?></td>
                        <td>
                            <span class="label label-sm <?= (int)($row->status ?? 0) === 1 ? 'label-success' : 'label-danger' ?>">
                                <?= (int)($row->status ?? 0) === 1 ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td><?= ($row->create_date) ? $row->create_date->format("d M Y") : '' ?></td>
                        <td>
                            <a href="bts.php?id=<?= $row->id ?>" class="btn btn-xs btn-primary">
                                <i class="fa fa-pencil"></i> Edit
                            </a>
                            <a href="#" class="btn btn-xs btn-danger delete-bts" data-id="<?= $row->id ?>" data-title="<?= htmlspecialchars($row->title ?? '') ?>" data-token="<?= $csrf_token ?>">
                                <i class="fa fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($paginator->hasPages()): ?>
<div class="pagination-container text-center">
    <ul class="pagination">
        <?php for ($i = 1; $i <= $paginator->lastPage(); $i++): ?>
            <li class="<?= ($i == $paginator->currentPage()) ? 'active' : '' ?>">
                <a href="#" class="pagination-link" data-page="<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</div>
<?php endif; ?>