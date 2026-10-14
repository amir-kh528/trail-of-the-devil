<?php
require __DIR__ . '/config/db.php';

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

$sql = 'SELECT id, name, email, message, created_at, is_read FROM contact_messages';
if ($where !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY is_read ASC, created_at DESC';

$stmt = getDb()->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

$unreadCount = count(array_filter($messages, static fn ($message) => (int) ($message['is_read'] ?? 0) === 0));
$readCount = count($messages) - $unreadCount;
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>داشبورد پیام‌ها</title>
    <link rel="stylesheet" href="fonts/fonts.css">
    <style>

        :root {
            --bg: #f5f7ff;
            --panel: #ffffff;
            --soft: #f1f5ff;
            --primary: #4f46e5;
            --primary-strong: #3730a3;
            --primary-soft: rgba(79, 70, 229, 0.1);
            --text: #1f2937;
            --muted: #64748b;
            --line: #e5e7eb;
            --line-soft: #edf2f7;
            --success-bg: #ecfdf5;
            --success-text: #15803d;
            --warning-bg: #fff7ed;
            --warning-text: #c2410c;
            --danger-bg: #fef2f2;
            --danger-text: #b91c1c;
            --shadow: 0 16px 40px rgba(31, 41, 55, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Vazirmatn', 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(180deg, #f7f8ff 0%, #eef4ff 100%);
            color: var(--text);
            line-height: 1.7;
            padding: 32px 18px;
            font-size: 14px;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.82);
            border: 1px solid var(--line);
            border-radius: 22px;
            box-shadow: var(--shadow);
            padding: 24px 18px 30px;
            backdrop-filter: blur(10px);
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.8rem, 2.8vw, 2.7rem);
            color: var(--text);
        }

        .header-tag {
            background: var(--primary-soft);
            color: var(--primary-strong);
            border: 1px solid rgba(79, 70, 229, 0.12);
            border-radius: 999px;
            padding: 8px 12px;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            font-size: 0.8rem;
            font-weight: 800;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(140px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--panel);
            border: 1px solid var(--line-soft);
            border-radius: 16px;
            padding: 16px;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.03);
        }

        .stat-label {
            display: block;
            color: var(--muted);
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            font-size: 0.82rem;
            margin-bottom: 6px;
        }

        .stat-value {
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text);
        }

        .status {
            display: none;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .status.show {
            display: block;
        }

        .status.success {
            background: var(--success-bg);
            color: var(--success-text);
            border-color: rgba(21, 128, 61, 0.15);
        }

        .status.error {
            background: var(--danger-bg);
            color: var(--danger-text);
            border-color: rgba(185, 28, 28, 0.12);
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: end;
            gap: 12px;
            background: var(--panel);
            border: 1px solid var(--line-soft);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .filters {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filters input,
        .filters select {
            border: 1px solid var(--line);
            border-radius: 10px;
            min-height: 42px;
            padding: 0 12px;
            font: inherit;
            background: #fff;
            color: var(--text);
        }

        .filters input {
            width: min(320px, 70vw);
        }

        .btn,
        .tiny-btn,
        .bulk-btn {
            border: 1px solid rgba(148, 163, 184, 0.5);
            border-radius: 10px;
            cursor: pointer;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            font-weight: 700;
            font-size: 0.82rem;
            transition: none;
            margin: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            letter-spacing: 0;
            background: #fff;
            color: var(--text);
        }

        .btn {
            min-height: 38px;
            padding: 0.58rem 0.9rem;
        }

        .btn.primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-strong) 100%);
            color: #fff;
            border-color: rgba(79, 70, 229, 0.45);
        }

        .btn.secondary {
            background: #f8fafc;
            color: var(--text);
            border: 1px solid rgba(148, 163, 184, 0.5);
        }

        .tiny-btn {
            min-height: 32px;
            padding: 0.46rem 0.72rem;
            line-height: 1.2;
            min-width: 66px;
            background: #fff;
            border-color: rgba(148, 163, 184, 0.5);
            font-size: 0.78rem;
        }

        .edit-btn {
            background: #20a5e2;
            color: #fff;
            border-color: rgba(79, 70, 229, 0.32);
        }

        .delete-btn {
            background: #c51138;
            color: #fff;
            border-color: rgba(190, 24, 93, 0.28);
        }

        .bulk-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
            padding: 10px 12px;
            background: #f8faff;
            border: 1px solid var(--line-soft);
            border-radius: 12px;
            flex-wrap: wrap;
        }

        .bulk-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .bulk-btn {
            min-height: 32px;
            padding: 0.42rem 0.8rem;
            min-width: 116px;
            background: #fff;
            color: var(--text);
            border: 1px solid rgba(148, 163, 184, 0.5);
            border-radius: 10px;
            box-shadow: none;
            line-height: 1.2;
            font-size: 0.74rem;
            font-weight: 600;
        }

        .bulk-btn.delete {
            color: #fff;
            border-color: #f26582;
            background: linear-gradient(135deg, #ee5a7a 0%, #df3d5a 100%);
        }

        .bulk-btn.read {
            color: #fff;
            border-color: #38b57c;
            background: linear-gradient(135deg, #2fc285 0%, #1fa067 100%);
        }

        .bulk-btn.unread {
            color: #fff;
            border-color: #f0af63;
            background: linear-gradient(135deg, #f3b25a 0%, #e68c3d 100%);
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid rgba(148, 163, 184, 0.42);
            border-radius: 14px;
            background: var(--panel);
            box-shadow: inset 0 0 0 1px rgba(148, 163, 184, 0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 980px;
        }

        th, td {
            border-bottom: 1px solid rgba(148, 163, 184, 0.32);
            border-left: 1px solid rgba(148, 163, 184, 0.2);
            padding: 10px 8px;
            text-align: right;
            vertical-align: middle;
            font-size: 0.85rem;
            line-height: 1.5;
        }

        th {
            border-top: 1px solid rgba(148, 163, 184, 0.14);
        }

        th:last-child,
        td:last-child {
            border-left: none;
        }

        thead th {
            background: #f8faff;
            color: var(--primary-strong);
            font-size: 0.83rem;
            white-space: nowrap;
            font-weight: 800;
            letter-spacing: 0;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
        }

        thead th,
        tbody td {
            font-family: 'Vazirmatn', Tahoma, sans-serif;
        }

        tbody tr {
            transition: background 0.2s ease;
        }

        tbody tr:hover {
            background: rgba(79, 70, 229, 0.02);
        }

        tbody tr.unread-row {
            background: rgba(255, 255, 255, 0.7);
        }

        tbody tr.read-row {
            background: rgba(248, 250, 252, 0.7);
        }

        .cb-col,
        .index-col,
        .status-col {
            width: 60px;
        }

        .message-cell {
            max-width: 360px;
            white-space: pre-wrap;
            word-break: break-word;
            font-family: 'Vazirmatn', Tahoma, sans-serif;
            line-height: 1.9;
            color: var(--text);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .status-pill.read {
            background: var(--success-bg);
            color: var(--success-text);
        }

        .status-pill.unread {
            background: var(--warning-bg);
            color: var(--warning-text);
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .checkbox {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
        }

        .empty {
            background: #fafbff;
            border: 1px dashed var(--line);
            border-radius: 14px;
            text-align: center;
            color: var(--muted);
            padding: 36px 16px;
            font-weight: 700;
        }

        .edit-form-row {
            display: none;
            background: #f8fbff;
        }

        .edit-form-row.open {
            display: table-row;
        }

        .edit-form-row td {
            padding: 16px;
        }

        .edit-form-inner {
            display: grid;
            grid-template-columns: 1fr 1fr 2fr auto;
            gap: 10px;
            align-items: end;
        }

        .edit-form-inner input,
        .edit-form-inner textarea {
            width: 100%;
            min-height: 42px;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 0.72rem 0.8rem;
            font: inherit;
            background: white;
            resize: vertical;
        }

        .save-btn,
        .cancel-btn {
            min-height: 42px;
            padding: 0.7rem 0.9rem;
            border: 1px solid transparent;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
        }

        .save-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-strong) 100%);
            color: #fff;
        }

        .cancel-btn {
            background: linear-gradient(180deg, #f8fafc, #eef2f7);
            color: var(--text);
            border-color: rgba(148, 163, 184, 0.15);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>داشبورد پیام‌ها</h1>
            <span class="header-tag">مدیریت پیام‌های دریافتی</span>
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
                            <tr data-id="<?php echo (int) $message['id']; ?>" class="<?php echo $isRead ? 'read-row' : 'unread-row'; ?>">
                                <td><input type="checkbox" class="message-select checkbox" value="<?php echo (int) $message['id']; ?>" aria-label="انتخاب پیام <?php echo (int) $message['id']; ?>"></td>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($message['name']); ?></td>
                                <td><?php echo htmlspecialchars($message['email']); ?></td>
                                <td class="message-cell"><?php echo nl2br(htmlspecialchars($message['message'])); ?></td>
                                <td><?php echo htmlspecialchars($message['created_at']); ?></td>
                                <td>
                                    <span class="status-pill <?php echo $isRead ? 'read' : 'unread'; ?>">
                                        <?php echo $isRead ? 'خوانده‌شده' : 'نخوانده'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <button type="button" class="tiny-btn edit-btn" data-id="<?php echo (int) $message['id']; ?>">ویرایش</button>
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
