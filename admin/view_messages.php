<?php
require __DIR__ . '/../config/db.php';
session_start();

function isAjaxRequest(): bool
{
    return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function normalizeSelectedIds(mixed $value): array
{
    if (!is_array($value)) {
        $value = [$value];
    }

    $ids = [];
    foreach ($value as $item) {
        $id = filter_var($item, FILTER_VALIDATE_INT);
        if ($id !== false && $id > 0) {
            $ids[] = (int) $id;
        }
    }

    return array_values(array_unique($ids));
}

if ((!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true)) {
    if (isAjaxRequest()) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(403);
        echo json_encode(['success' => false, 'redirect' => 'login.php']);
        exit;
    }

    header('Location: login.php');
    exit;
}

initializeDatabase();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $selectedIds = normalizeSelectedIds($_POST['selected_ids'] ?? []);

    if ($action === 'delete' && $id) {
        $stmt = getDb()->prepare('DELETE FROM contact_messages WHERE id = ?');
        $stmt->execute([$id]);

        if (isAjaxRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: view_messages.php');
        exit;
    }

    if ($action === 'update' && $id) {
        $name = trim((string) ($_POST['name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));

        if ($name !== '' && $email !== '' && $message !== '') {
            $stmt = getDb()->prepare(
                'UPDATE contact_messages SET name = ?, email = ?, message = ? WHERE id = ?'
            );
            $stmt->execute([$name, $email, $message, $id]);

            if (isAjaxRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true]);
                exit;
            }

            header('Location: view_messages.php');
            exit;
        }

        if (isAjaxRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => 'تمام فیلدها باید پر شوند.']);
            exit;
        }
    }

    if ($action === 'bulk_delete' && $selectedIds !== []) {
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
        $stmt = getDb()->prepare('DELETE FROM contact_messages WHERE id IN (' . $placeholders . ')');
        $stmt->execute($selectedIds);

        if (isAjaxRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'deleted' => count($selectedIds)]);
            exit;
        }

        header('Location: view_messages.php');
        exit;
    }

    if ($action === 'bulk_toggle_read' && $selectedIds !== []) {
        $readState = (int) ($_POST['read_state'] ?? 0);
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
        $stmt = getDb()->prepare('UPDATE contact_messages SET is_read = ? WHERE id IN (' . $placeholders . ')');
        $stmt->execute([$readState, ...$selectedIds]);

        if (isAjaxRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'updated' => count($selectedIds)]);
            exit;
        }

        header('Location: view_messages.php');
        exit;
    }

    if ($action === 'toggle_read' && $id) {
        $readState = (int) ($_POST['read_state'] ?? 0);
        $stmt = getDb()->prepare('UPDATE contact_messages SET is_read = ? WHERE id = ?');
        $stmt->execute([$readState, $id]);

        if (isAjaxRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: view_messages.php');
        exit;
    }

    if ($action === 'bulk_toggle_responded' && $selectedIds !== []) {
        $respondedState = (int) ($_POST['responded_state'] ?? 0);
        $placeholders = implode(',', array_fill(0, count($selectedIds), '?'));
        $stmt = getDb()->prepare('UPDATE contact_messages SET is_responded = ? WHERE id IN (' . $placeholders . ')');
        $stmt->execute([$respondedState, ...$selectedIds]);

        if (isAjaxRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true, 'updated' => count($selectedIds)]);
            exit;
        }

        header('Location: view_messages.php');
        exit;
    }

    if ($action === 'toggle_responded' && $id) {
        $respondedState = (int) ($_POST['responded_state'] ?? 0);
        $stmt = getDb()->prepare('UPDATE contact_messages SET is_responded = ? WHERE id = ?');
        $stmt->execute([$respondedState, $id]);

        if (isAjaxRequest()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => true]);
            exit;
        }

        header('Location: view_messages.php');
        exit;
    }
}

$searchTerm = trim((string) ($_GET['q'] ?? ''));
$searchField = $_GET['field'] ?? 'all';
if (!in_array($searchField, ['all', 'name', 'email'], true)) {
    $searchField = 'all';
}

$where = [];
$params = [];

if ($searchTerm !== '') {
    if ($searchField === 'name') {
        $where[] = 'name LIKE ?';
        $params[] = '%' . $searchTerm . '%';
    } elseif ($searchField === 'email') {
        $where[] = 'email LIKE ?';
        $params[] = '%' . $searchTerm . '%';
    } else {
        $where[] = '(name LIKE ? OR email LIKE ? OR message LIKE ?)';
        $params[] = '%' . $searchTerm . '%';
        $params[] = '%' . $searchTerm . '%';
        $params[] = '%' . $searchTerm . '%';
    }
}

$sql = 'SELECT id, name, email, message, created_at, is_read, is_responded FROM contact_messages';
if ($where !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY is_read ASC, is_responded ASC, created_at DESC';

$stmt = getDb()->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

$unreadCount = count(array_filter($messages, static fn ($message) => (int) ($message['is_read'] ?? 0) === 0));
$readCount = count($messages) - $unreadCount;
$respondedCount = count(array_filter($messages, static fn ($message) => (int) ($message['is_responded'] ?? 0) === 1));
$pendingResponseCount = count($messages) - $respondedCount;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>داشبورد پیام‌ها</title>
    <link rel="stylesheet" href="../assets/fonts/fonts.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>داشبورد پیام‌ها</h1>
            <div class="header-actions">
                <span class="header-tag">مدیریت پیام‌های دریافتی</span>
                <form method="post" action="login.php" class="logout-form">
                    <input type="hidden" name="logout" value="1">
                    <button type="submit" class="btn secondary logout-btn">خروج</button>
                </form>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">کل پیام‌ها</span>
                <strong class="stat-value"><?php echo count($messages); ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">خوانده‌شده</span>
                <strong class="stat-value"><?php echo $readCount; ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">نخوانده</span>
                <strong class="stat-value"><?php echo $unreadCount; ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">پاسخ داده‌شده</span>
                <strong class="stat-value"><?php echo $respondedCount; ?></strong>
            </div>
        </div>

        <div id="adminStatus" class="status" aria-live="polite"></div>

        <div class="toolbar">
            <form method="get" class="filters" aria-label="جست‌وجو و فیلتر پیام‌ها">
                <input
                    type="search"
                    name="q"
                    value="<?php echo htmlspecialchars($searchTerm); ?>"
                    placeholder="جست‌وجو در پیام‌ها..."
                    aria-label="جست‌وجو در پیام‌ها"
                >
                <select name="field" aria-label="فیلتر بر اساس">
                    <option value="all" <?php echo $searchField === 'all' ? 'selected' : ''; ?>>همه فیلدها</option>
                    <option value="name" <?php echo $searchField === 'name' ? 'selected' : ''; ?>>نام</option>
                    <option value="email" <?php echo $searchField === 'email' ? 'selected' : ''; ?>>ایمیل</option>
                </select>
                <button type="submit" class="btn primary">جست‌وجو</button>
                <?php if ($searchTerm !== ''): ?>
                    <a href="view_messages.php" class="btn secondary" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">پاک کردن</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($messages)): ?>
            <div class="empty">هنوز پیامی ثبت نشده است.</div>
        <?php else: ?>
            <div class="bulk-toolbar">
                <label style="display:inline-flex; align-items:center; gap:8px; font-weight:700; color:var(--text);">
                    <input type="checkbox" id="select-all" class="checkbox" aria-label="انتخاب همه">
                    انتخاب همه
                </label>
                <div class="bulk-actions">
                    <button type="button" class="bulk-btn delete" data-action="bulk_delete">حذف انتخاب‌شده</button>
                    <button type="button" class="bulk-btn read" data-action="bulk_toggle_read" data-read-state="1">علامت‌گذاری خوانده</button>
                    <button type="button" class="bulk-btn unread" data-action="bulk_toggle_read" data-read-state="0">علامت‌گذاری نخوانده</button>
                    <button type="button" class="bulk-btn responded" data-action="bulk_toggle_responded" data-responded-state="1">پاسخ داده‌شده</button>
                    <button type="button" class="bulk-btn pending" data-action="bulk_toggle_responded" data-responded-state="0">در انتظار پاسخ</button>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th class="cb-col"><input type="checkbox" id="select-all-header" class="checkbox" aria-label="انتخاب همه ردیف‌ها"></th>
                            <th class="index-col">ردیف</th>
                            <th>نام</th>
                            <th>ایمیل</th>
                            <th>پیام</th>
                            <th>تاریخ</th>
                            <th class="status-col">وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($messages as $index => $message): ?>
                            <?php $isRead = (int) ($message['is_read'] ?? 0); ?>
                            <?php $isResponded = (int) ($message['is_responded'] ?? 0); ?>
                            <tr data-id="<?php echo (int) $message['id']; ?>" class="<?php echo $isRead ? 'read-row' : 'unread-row'; ?>">
                                <td><input type="checkbox" class="message-select checkbox" value="<?php echo (int) $message['id']; ?>" aria-label="انتخاب پیام <?php echo (int) $message['id']; ?>"></td>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($message['name']); ?></td>
                                <td><?php echo htmlspecialchars($message['email']); ?></td>
                                <td class="message-cell"><?php echo nl2br(htmlspecialchars($message['message'])); ?></td>
                                <td><?php echo htmlspecialchars($message['created_at']); ?></td>
                                <td>
                                    <div class="status-stack">
                                        <span class="status-pill <?php echo $isRead ? 'read' : 'unread'; ?>">
                                            <?php echo $isRead ? 'خوانده‌شده' : 'نخوانده'; ?>
                                        </span>
                                        <span class="status-pill <?php echo $isResponded ? 'responded' : 'pending'; ?>">
                                            <?php echo $isResponded ? 'پاسخ داده‌شده' : 'در انتظار پاسخ'; ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="actions">
                                        <button type="button" class="tiny-btn edit-btn" data-id="<?php echo (int) $message['id']; ?>">ویرایش</button>
                                        <button type="button" class="tiny-btn read-btn <?php echo $isRead ? 'read' : 'unread'; ?>" data-id="<?php echo (int) $message['id']; ?>" data-read-state="<?php echo $isRead ? '0' : '1'; ?>"><?php echo $isRead ? 'بازگشت به نخوانده' : 'خوانده شد'; ?></button>
                                        <button type="button" class="tiny-btn respond-btn <?php echo $isResponded ? 'responded' : 'pending'; ?>" data-id="<?php echo (int) $message['id']; ?>" data-responded-state="<?php echo $isResponded ? '0' : '1'; ?>"><?php echo $isResponded ? 'بازگشت به انتظار' : 'پاسخ داده شد'; ?></button>
                                        <button type="button" class="tiny-btn delete-btn" data-id="<?php echo (int) $message['id']; ?>">حذف</button>
                                    </div>
                                </td>
                            </tr>
                            <tr class="edit-form-row" data-id="<?php echo (int) $message['id']; ?>" hidden>
                                <td colspan="8">
                                    <form class="edit-form-inner" data-id="<?php echo (int) $message['id']; ?>">
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($message['name']); ?>" aria-label="نام">
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($message['email']); ?>" aria-label="ایمیل">
                                        <textarea name="message" rows="3" aria-label="پیام"><?php echo htmlspecialchars($message['message']); ?></textarea>
                                        <div class="actions">
                                            <button type="submit" class="save-btn">ذخیره</button>
                                            <button type="button" class="cancel-btn">انصراف</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const statusBox = document.getElementById('adminStatus');
            const selectAllCheckboxes = [
                document.getElementById('select-all'),
                document.getElementById('select-all-header')
            ].filter(Boolean);

            const showStatus = (message, isSuccess = true) => {
                statusBox.textContent = message;
                statusBox.className = 'status show ' + (isSuccess ? 'success' : 'error');
            };

            const syncSelectAll = () => {
                const rows = [...document.querySelectorAll('.message-select')];
                const checkedRows = rows.filter((box) => box.checked).length;
                const allChecked = rows.length > 0 && checkedRows === rows.length;
                selectAllCheckboxes.forEach((checkbox) => {
                    checkbox.checked = allChecked;
                });
            };

            selectAllCheckboxes.forEach((checkbox) => {
                checkbox.addEventListener('change', () => {
                    document.querySelectorAll('.message-select').forEach((item) => {
                        item.checked = checkbox.checked;
                    });
                });
            });

            document.querySelectorAll('.message-select').forEach((checkbox) => {
                checkbox.addEventListener('change', syncSelectAll);
            });

            const readSelected = async (readState) => {
                const selected = [...document.querySelectorAll('.message-select:checked')].map((input) => input.value);

                if (!selected.length) {
                    showStatus('هیچ پیامی انتخاب نشده است.', false);
                    return;
                }

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: (() => {
                        const params = new URLSearchParams();
                        params.set('action', 'bulk_toggle_read');
                        params.set('read_state', String(readState));
                        selected.forEach((id) => params.append('selected_ids[]', id));
                        return params;
                    })()
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    showStatus('تغییر وضعیت انجام نشد.', false);
                }
            };

            const respondedSelected = async (respondedState) => {
                const selected = [...document.querySelectorAll('.message-select:checked')].map((input) => input.value);

                if (!selected.length) {
                    showStatus('هیچ پیامی انتخاب نشده است.', false);
                    return;
                }

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: (() => {
                        const params = new URLSearchParams();
                        params.set('action', 'bulk_toggle_responded');
                        params.set('responded_state', String(respondedState));
                        selected.forEach((id) => params.append('selected_ids[]', id));
                        return params;
                    })()
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    showStatus('تغییر وضعیت پاسخ انجام نشد.', false);
                }
            };

            const bulkDelete = async () => {
                const selected = [...document.querySelectorAll('.message-select:checked')].map((input) => input.value);

                if (!selected.length) {
                    showStatus('هیچ پیامی برای حذف انتخاب نشده است.', false);
                    return;
                }

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: (() => {
                        const params = new URLSearchParams();
                        params.set('action', 'bulk_delete');
                        selected.forEach((id) => params.append('selected_ids[]', id));
                        return params;
                    })()
                });

                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    showStatus('حذف دسته‌جمعی انجام نشد.', false);
                }
            };

            document.querySelectorAll('.bulk-btn').forEach((button) => {
                button.addEventListener('click', async () => {
                    const action = button.dataset.action;
                    if (action === 'bulk_delete') {
                        await bulkDelete();
                        return;
                    }
                    if (action === 'bulk_toggle_read') {
                        await readSelected(Number(button.dataset.readState));
                        return;
                    }
                    if (action === 'bulk_toggle_responded') {
                        await respondedSelected(Number(button.dataset.respondedState));
                    }
                });
            });

            document.querySelectorAll('.read-btn').forEach((button) => {
                button.addEventListener('click', async () => {
                    const id = button.dataset.id;
                    const readState = Number(button.dataset.readState);
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({ action: 'toggle_read', id, read_state: String(readState) })
                    });

                    const result = await response.json();
                    if (result.success) {
                        window.location.reload();
                    } else {
                        showStatus('تغییر وضعیت خوانده‌شده انجام نشد.', false);
                    }
                });
            });

            document.querySelectorAll('.respond-btn').forEach((button) => {
                button.addEventListener('click', async () => {
                    const id = button.dataset.id;
                    const respondedState = Number(button.dataset.respondedState);
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({ action: 'toggle_responded', id, responded_state: String(respondedState) })
                    });

                    const result = await response.json();
                    if (result.success) {
                        window.location.reload();
                    } else {
                        showStatus('تغییر وضعیت پاسخ انجام نشد.', false);
                    }
                });
            });

            document.querySelectorAll('.delete-btn').forEach((button) => {
                button.addEventListener('click', async () => {
                    const id = button.dataset.id;
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({ action: 'delete', id })
                    });

                    const result = await response.json();

                    if (result.success) {
                        const row = button.closest('tr');
                        if (row) {
                            row.remove();
                        }
                        const editorRow = document.querySelector('.edit-form-row[data-id="' + id + '"]');
                        if (editorRow) {
                            editorRow.remove();
                        }
                        showStatus('پیام مورد نظر حذف شد.');
                    } else {
                        showStatus('حذف پیام انجام نشد.', false);
                    }
                });
            });

            document.querySelectorAll('.edit-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.id;
                    const editorRow = document.querySelector('.edit-form-row[data-id="' + id + '"]');
                    if (editorRow) {
                        const isOpen = !editorRow.hidden;
                        editorRow.hidden = isOpen;
                        editorRow.classList.toggle('open', !isOpen);
                    }
                });
            });

            document.querySelectorAll('.cancel-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const row = button.closest('.edit-form-row');
                    if (row) {
                        row.hidden = true;
                        row.classList.remove('open');
                    }
                });
            });

            document.querySelectorAll('.edit-form-inner').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    const id = form.dataset.id;
                    const formData = new FormData(form);
                    formData.append('action', 'update');
                    formData.append('id', id);

                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        const row = document.querySelector('tr[data-id="' + id + '"]');
                        if (row) {
                            const nameInput = form.querySelector('input[name="name"]');
                            const emailInput = form.querySelector('input[name="email"]');
                            const messageInput = form.querySelector('textarea[name="message"]');
                            row.children[2].textContent = nameInput.value;
                            row.children[3].textContent = emailInput.value;
                            row.children[4].innerHTML = messageInput.value.replace(/\n/g, '<br>');
                        }

                        const editorRow = form.closest('.edit-form-row');
                        if (editorRow) {
                            editorRow.hidden = true;
                            editorRow.classList.remove('open');
                        }
                        showStatus('پیام با موفقیت ویرایش شد.');
                    } else {
                        showStatus(result.message || 'ویرایش انجام نشد.', false);
                    }
                });
            });
        });
    </script>
</body>
</html>
