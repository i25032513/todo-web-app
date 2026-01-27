<?php
session_start();
include 'db/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ADD note */
if (isset($_POST['add_note'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $color = $_POST['color'];

    if ($title && $content) {
        $stmt = $conn->prepare("
            INSERT INTO sticky_notes (user_id, title, content, color)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $user_id, $title, $content, $color);
        $stmt->execute();
        $stmt->close();
    }
}

/* UPDATE note */
if (isset($_POST['update_note'])) {
    $id = (int)$_POST['note_id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $color = $_POST['color'];

    $stmt = $conn->prepare("
        UPDATE sticky_notes
        SET title = ?, content = ?, color = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("sssii", $title, $content, $color, $id, $user_id);
    $stmt->execute();
    $stmt->close();
}

/* DELETE note */
if (isset($_POST['delete_note'])) {
    $id = (int)$_POST['note_id'];

    $stmt = $conn->prepare("
        DELETE FROM sticky_notes
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
}

/* FETCH notes */
$stmt = $conn->prepare("
    SELECT * FROM sticky_notes
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$notes = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sticky Wall</title>
<link rel="stylesheet" href="css/style.css">

<style>
.sticky-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
}

.sticky {
    padding: 15px;
    border-radius: 10px;
    min-height: 160px;
    box-shadow: 0 4px 10px rgba(0,0,0,.1);
    cursor: pointer;
    position: relative;
}

.yellow { background: #fff3b0; }
.blue { background: #dbeafe; }
.pink { background: #fde2e2; }
.orange { background: #fed7aa; }

.add-sticky {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    background: #f1f1f1;
    color: #555;
}

/* DELETE ICON */
.delete-btn {
    position: absolute;
    top: 8px;
    right: 10px;
    color: #b91c1c;
    font-weight: bold;
    cursor: pointer;
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.4);
    align-items: center;
    justify-content: center;
    z-index: 999;
}

.modal-content {
    background: #fff;
    padding: 25px;
    width: 360px;
    border-radius: 12px;
}

.modal-content input,
.modal-content textarea,
.modal-content select {
    width: 100%;
    margin-bottom: 12px;
    padding: 8px;
}
</style>
</head>

<body>
<div class="wrapper">
<?php include 'sidebar.php'; ?>

<div class="main">
    <div class="header">
        <h3>Sticky Wall</h3>
    </div>

    <div class="sticky-grid">
        <!-- ADD -->
        <div class="sticky add-sticky" onclick="openAdd()">+</div>

        <!-- NOTES -->
        <?php while ($n = $notes->fetch_assoc()): ?>
            <div class="sticky <?php echo $n['color']; ?>"
                 onclick="openEdit(
                     <?php echo $n['id']; ?>,
                     '<?php echo htmlspecialchars(addslashes($n['title'])); ?>',
                     '<?php echo htmlspecialchars(addslashes($n['content'])); ?>',
                     '<?php echo $n['color']; ?>'
                 )">

                <!-- DELETE -->
                <span class="delete-btn"
                      onclick="confirmDelete(event, <?php echo $n['id']; ?>)">×</span>

                <h4><?php echo htmlspecialchars($n['title']); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($n['content'])); ?></p>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</div>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <h3>Add Sticky Note</h3>
        <form method="post">
            <input name="title" placeholder="Title" required>
            <textarea name="content" placeholder="Note content" required></textarea>
            <select name="color">
                <option value="yellow">Yellow</option>
                <option value="blue">Blue</option>
                <option value="pink">Pink</option>
                <option value="orange">Orange</option>
            </select>
            <button class="btn" name="add_note">Save</button>
            <button type="button" onclick="closeAll()">Cancel</button>
        </form>
    </div>
</div>

<!-- EDIT / DELETE MODAL -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <h3>Edit Sticky Note</h3>
        <form method="post">
            <input type="hidden" name="note_id" id="note_id">
            <input name="title" id="edit_title" required>
            <textarea name="content" id="edit_content" required></textarea>
            <select name="color" id="edit_color">
                <option value="yellow">Yellow</option>
                <option value="blue">Blue</option>
                <option value="pink">Pink</option>
                <option value="orange">Orange</option>
            </select>

            <button class="btn" name="update_note">Update</button>
            <button class="btn" name="delete_note"
                    onclick="return confirm('Delete this note?')"
                    style="background:#b91c1c;">Delete</button>

            <button type="button" onclick="closeAll()">Cancel</button>
        </form>
    </div>
</div>

<script>
function openAdd() {
    document.getElementById('addModal').style.display = 'flex';
}

function openEdit(id, title, content, color) {
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('note_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_content').value = content;
    document.getElementById('edit_color').value = color;
}

function closeAll() {
    document.getElementById('addModal').style.display = 'none';
    document.getElementById('editModal').style.display = 'none';
}

/* Delete via X icon */
function confirmDelete(event, id) {
    event.stopPropagation();
    if (confirm("Delete this note?")) {
        const form = document.createElement('form');
        form.method = 'post';
        form.innerHTML = `
            <input type="hidden" name="note_id" value="${id}">
            <input type="hidden" name="delete_note" value="1">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

</body>
</html>
