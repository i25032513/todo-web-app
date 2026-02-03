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
        if ($stmt->execute()) {
            $_SESSION['sticky_success'] = 'Sticky note added successfully!';
        }
        $stmt->close();
        header("Location: sticky_wall.php");
        exit();
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
    if ($stmt->execute()) {
        $_SESSION['sticky_success'] = 'Sticky note updated successfully!';
    }
    $stmt->close();
    header("Location: sticky_wall.php");
    exit();
}

/* DELETE note */
if (isset($_POST['delete_note'])) {
    $id = (int)$_POST['note_id'];

    $stmt = $conn->prepare("
        DELETE FROM sticky_notes
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $id, $user_id);
    if ($stmt->execute()) {
        $_SESSION['sticky_success'] = 'Sticky note deleted successfully!';
    }
    $stmt->close();
    header("Location: sticky_wall.php");
    exit();
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

// Get success message if exists
$sticky_success = $_SESSION['sticky_success'] ?? '';
unset($_SESSION['sticky_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sticky Wall - ToDo Student</title>
<link rel="stylesheet" href="css/style.css">

<style>
.sticky-wall-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, var(--warning-color), #ffa500);
    border-radius: 16px;
    color: white;
    box-shadow: var(--shadow-md);
}

.sticky-wall-header h3 {
    color: white;
    margin: 0;
    font-size: 28px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.note-count {
    background: rgba(255, 255, 255, 0.2);
    padding: 10px 20px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 15px;
}

.sticky-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.sticky {
    padding: 20px;
    border-radius: 16px;
    min-height: 200px;
    box-shadow: 0 4px 15px rgba(0,0,0,.12);
    cursor: pointer;
    position: relative;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    animation: fadeIn 0.4s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.sticky:hover {
    transform: translateY(-8px) rotate(1deg);
    box-shadow: 0 8px 25px rgba(0,0,0,.18);
}

.sticky h4 {
    margin: 0 0 12px 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    word-break: break-word;
}

.sticky p {
    margin: 0;
    font-size: 14px;
    line-height: 1.6;
    color: #555;
    word-break: break-word;
    max-height: 120px;
    overflow-y: auto;
}

/* Light Mode Sticky Colors */
.yellow { 
    background: linear-gradient(135deg, #fff9c4, #fff59d);
    border-color: #ffd54f;
}

.blue { 
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-color: #90caf9;
}

.pink { 
    background: linear-gradient(135deg, #fce4ec, #f8bbd0);
    border-color: #f48fb1;
}

.orange { 
    background: linear-gradient(135deg, #ffe0b2, #ffcc80);
    border-color: #ffb74d;
}

.green {
    background: linear-gradient(135deg, #c8e6c9, #a5d6a7);
    border-color: #81c784;
}

.purple {
    background: linear-gradient(135deg, #e1bee7, #ce93d8);
    border-color: #ba68c8;
}

/* Dark Mode Sticky Colors */
body.dark-mode .sticky h4 {
    color: #e0e0e0;
}

body.dark-mode .sticky p {
    color: #d0d0d0;
}

body.dark-mode .yellow { 
    background: linear-gradient(135deg, #b8860b, #daa520);
    border-color: #ffd700;
}

body.dark-mode .blue { 
    background: linear-gradient(135deg, #1e3a5f, #2c5282);
    border-color: #4682b4;
}

body.dark-mode .pink { 
    background: linear-gradient(135deg, #7b2d5f, #a0417d);
    border-color: #c45a9c;
}

body.dark-mode .orange { 
    background: linear-gradient(135deg, #b8651b, #d97f28);
    border-color: #ff8c42;
}

body.dark-mode .green {
    background: linear-gradient(135deg, #2d5f3e, #3d7a4f);
    border-color: #5cb85c;
}

body.dark-mode .purple {
    background: linear-gradient(135deg, #5e3a6e, #7d4b8e);
    border-color: #9b6bb8;
}

.add-sticky {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 64px;
    background: linear-gradient(135deg, #f5f5f5, #e0e0e0);
    color: var(--primary-color);
    font-weight: 300;
    border: 3px dashed var(--border-color);
    transition: all 0.3s ease;
}

.add-sticky:hover {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-color: var(--primary-color);
    color: var(--primary-dark);
    transform: translateY(-8px) scale(1.02);
}

/* Dark Mode Add Sticky */
body.dark-mode .add-sticky {
    background: linear-gradient(135deg, #2d3748, #374151);
    color: #60a5fa;
}

body.dark-mode .add-sticky:hover {
    background: linear-gradient(135deg, #1e3a5f, #2c5282);
    border-color: #60a5fa;
    color: #93c5fd;
}

/* DELETE ICON */
.delete-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 28px;
    height: 28px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--danger-color);
    font-weight: bold;
    font-size: 20px;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 10;
}

.delete-btn:hover {
    background: var(--danger-color);
    color: white;
    transform: scale(1.15);
}

/* Dark Mode Delete Button */
body.dark-mode .delete-btn {
    background: rgba(30, 30, 30, 0.9);
    color: #ff6b6b;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

body.dark-mode .delete-btn:hover {
    background: #dc2626;
    color: white;
}

/* DATE TAG */
.note-date {
    position: absolute;
    bottom: 12px;
    right: 12px;
    font-size: 11px;
    color: rgba(0,0,0,0.4);
    font-weight: 500;
}

body.dark-mode .note-date {
    color: rgba(255,255,255,0.5);
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.6);
    align-items: center;
    justify-content: center;
    z-index: 999;
    animation: fadeInModal 0.3s ease;
}

@keyframes fadeInModal {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.modal-content {
    background: #fff;
    padding: 30px;
    width: 90%;
    max-width: 450px;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    animation: slideUp 0.3s ease;
}

body.dark-mode .modal-content {
    background: #1e293b;
    border: 1px solid #374151;
}

body.dark-mode .modal-content h3 {
    color: #60a5fa;
}

body.dark-mode .modal-content input,
body.dark-mode .modal-content textarea,
body.dark-mode .modal-content select {
    background: #0f172a;
    color: #e2e8f0;
    border-color: #475569;
}

body.dark-mode .modal-content input:focus,
body.dark-mode .modal-content textarea:focus,
body.dark-mode .modal-content select:focus {
    background: #1e293b;
    border-color: #60a5fa;
}

@keyframes slideUp {
    from {
        transform: translateY(50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-content h3 {
    margin: 0 0 20px 0;
    color: var(--primary-color);
    font-size: 24px;
}

.modal-content input,
.modal-content textarea,
.modal-content select {
    width: 100%;
    margin-bottom: 15px;
    padding: 12px 15px;
    border: 2px solid var(--border-color);
    border-radius: 10px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s ease;
}

.modal-content input:focus,
.modal-content textarea:focus,
.modal-content select:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
}

.modal-content textarea {
    min-height: 120px;
    resize: vertical;
}

.modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.modal-actions button {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    font-family: 'Poppins', sans-serif;
}

.btn-save {
    background: var(--primary-color);
    color: white;
}

.btn-save:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

.btn-delete {
    background: var(--danger-color);
    color: white;
}

.btn-delete:hover {
    background: #c41828;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(231, 29, 54, 0.3);
}

.btn-cancel {
    background: #f5f5f5;
    color: var(--text-color);
}

.btn-cancel:hover {
    background: #e0e0e0;
}

/* Color picker preview */
.color-preview {
    display: inline-block;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    margin-left: 10px;
    vertical-align: middle;
    border: 2px solid var(--border-color);
}

/* Flash message */
.flash-message {
    background: var(--success-color);
    color: white;
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: var(--shadow-md);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.flash-close {
    background: none;
    border: none;
    color: white;
    font-size: 20px;
    cursor: pointer;
    padding: 0;
    width: 25px;
    height: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: var(--shadow-sm);
}

.empty-state-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.3;
}

.empty-state h4 {
    color: var(--text-color);
    margin-bottom: 10px;
}

.empty-state p {
    color: var(--text-muted);
    margin-bottom: 20px;
}
</style>
</head>

<body>
<div class="wrapper">
<?php include 'sidebar.php'; ?>

<div class="main">
    <?php if ($sticky_success): ?>
        <div class="flash-message" id="flashMessage">
            <span>✓ <?php echo htmlspecialchars($sticky_success); ?></span>
            <button class="flash-close" onclick="closeFlash()">×</button>
        </div>
    <?php endif; ?>

    <div class="sticky-wall-header">
        <h3>📌 Sticky Wall</h3>
        <div class="note-count"><?php echo $notes->num_rows; ?> Notes</div>
    </div>

    <?php if ($notes->num_rows > 0 || true): ?>
    <div class="sticky-grid">
        <!-- ADD -->
        <div class="sticky add-sticky" onclick="openAdd()" title="Add New Sticky Note">+</div>

        <!-- NOTES -->
        <?php while ($n = $notes->fetch_assoc()): ?>
            <div class="sticky <?php echo htmlspecialchars($n['color']); ?>"
                 onclick="openEdit(
                     <?php echo $n['id']; ?>,
                     '<?php echo htmlspecialchars(addslashes($n['title'])); ?>',
                     '<?php echo htmlspecialchars(addslashes($n['content'])); ?>',
                     '<?php echo htmlspecialchars($n['color']); ?>'
                 )">

                <!-- DELETE -->
                <span class="delete-btn"
                      onclick="confirmDelete(event, <?php echo $n['id']; ?>)"
                      title="Delete note">×</span>

                <h4><?php echo htmlspecialchars($n['title']); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($n['content'])); ?></p>
                <div class="note-date"><?php echo date('M d, Y', strtotime($n['created_at'])); ?></div>
            </div>
        <?php endwhile; ?>
    </div>
    <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📌</div>
            <h4>No Sticky Notes Yet</h4>
            <p>Click the + button to create your first sticky note!</p>
        </div>
    <?php endif; ?>
</div>
</div>

<!-- ADD MODAL -->
<div class="modal" id="addModal">
    <div class="modal-content">
        <h3>📝 Add Sticky Note</h3>
        <form method="post">
            <input name="title" placeholder="Note Title" required maxlength="100">
            <textarea name="content" placeholder="What's on your mind?" required></textarea>
            <select name="color">
                <option value="yellow">🟨 Yellow</option>
                <option value="blue">🟦 Blue</option>
                <option value="pink">🟪 Pink</option>
                <option value="orange">🟧 Orange</option>
                <option value="green">🟩 Green</option>
                <option value="purple">🟣 Purple</option>
            </select>
            <div class="modal-actions">
                <button class="btn-save" name="add_note">Save Note</button>
                <button type="button" class="btn-cancel" onclick="closeAll()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT / DELETE MODAL -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <h3>✏️ Edit Sticky Note</h3>
        <form method="post">
            <input type="hidden" name="note_id" id="note_id">
            <input name="title" id="edit_title" placeholder="Note Title" required maxlength="100">
            <textarea name="content" id="edit_content" placeholder="Note content" required></textarea>
            <select name="color" id="edit_color">
                <option value="yellow">🟨 Yellow</option>
                <option value="blue">🟦 Blue</option>
                <option value="pink">🟪 Pink</option>
                <option value="orange">🟧 Orange</option>
                <option value="green">🟩 Green</option>
                <option value="purple">🟣 Purple</option>
            </select>

            <div class="modal-actions">
                <button class="btn-save" name="update_note">Update</button>
                <button class="btn-delete" name="delete_note" type="button"
                        onclick="confirmDeleteFromModal()">
                    Delete
                </button>
                <button type="button" class="btn-cancel" onclick="closeAll()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Custom Delete Modal -->
<div id="deleteStickyModal" class="modal">
    <div class="modal-content">
        <h4 style="margin-bottom:20px; color:#dc3545;">Confirm Deletion</h4>
        <p>Are you sure you want to delete this sticky note? This action cannot be undone.</p>
        <div style="margin-top:20px; display:flex; justify-content:center; gap:10px;">
            <form method="POST" id="deleteStickyForm" style="margin:0;">
                <input type="hidden" name="note_id" id="deleteStickyNoteId">
                <button type="submit" name="delete_note" class="btn btn-danger">Yes, Delete</button>
            </form>
            <button type="button" id="cancelStickyDelete" class="btn btn-secondary">Cancel</button>
        </div>
    </div>
</div>

<script>
function openAdd() {
    document.getElementById('addModal').style.display = 'flex';
    // Focus on title input
    setTimeout(() => {
        document.querySelector('#addModal input[name="title"]').focus();
    }, 100);
}

function openEdit(id, title, content, color) {
    document.getElementById('editModal').style.display = 'flex';
    document.getElementById('note_id').value = id;
    document.getElementById('edit_title').value = title;
    document.getElementById('edit_content').value = content;
    document.getElementById('edit_color').value = color;
    // Focus on title input
    setTimeout(() => {
        document.getElementById('edit_title').focus();
    }, 100);
}

function closeAll() {
    document.getElementById('addModal').style.display = 'none';
    document.getElementById('editModal').style.display = 'none';
}

/* Delete via X icon */
function confirmDelete(event, id) {
    event.stopPropagation();
    openStickyDeleteModal(id);
}

/* Delete from edit modal */
function confirmDeleteFromModal() {
    const noteId = document.getElementById('note_id').value;
    openStickyDeleteModal(noteId);
}

/* Close modal on ESC key */
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAll();
    }
});

/* Close modal on outside click */
document.getElementById('addModal').addEventListener('click', function(e) {
    if (e.target === this) closeAll();
});

document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeAll();
});

/* Flash message */
function closeFlash() {
    const flash = document.getElementById('flashMessage');
    if (flash) {
        flash.style.transition = 'opacity 0.3s, transform 0.3s';
        flash.style.opacity = '0';
        flash.style.transform = 'translateY(-20px)';
        setTimeout(() => flash.remove(), 300);
    }
}

// Auto-hide flash message after 5 seconds
const flashMessage = document.getElementById('flashMessage');
if (flashMessage) {
    setTimeout(() => closeFlash(), 5000);
}

// Delete sticky note modal
const deleteStickyModal = document.getElementById('deleteStickyModal');
const cancelStickyDelete = document.getElementById('cancelStickyDelete');
const deleteStickyForm = document.getElementById('deleteStickyForm');
const deleteStickyNoteId = document.getElementById('deleteStickyNoteId');

function openStickyDeleteModal(noteId) {
    deleteStickyNoteId.value = noteId;
    deleteStickyModal.style.display = 'flex';
}

cancelStickyDelete.addEventListener('click', () => {
    deleteStickyModal.style.display = 'none';
});

window.addEventListener('click', (e) => {
    if (e.target === deleteStickyModal) {
        deleteStickyModal.style.display = 'none';
    }
});
</script>

</body>
</html>
