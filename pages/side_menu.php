
<div id="menubar">
  <button id="toggleSidebar">☰</button>
</div>

<main class="container">
  <div class="sidebar" id="sidebar">
    <aside>
        <ul>
            <li><a href="?page=journal&action=add_sample"><i class="fas fa-database"></i> Add Sample Data</a></li>
            <li><a href="?page=journal&action=add_new"><i class="fas fa-pen"></i> Add New Entry</a></li>
            <li><a href="?page=journal&action=select_fields"><i class="fas fa-sliders-h"></i> Select Fields</a></li>
        </ul>
    </aside>
  </div>
  <div class="content">
    <p>This content takes up the remaining space and adjusts dynamically.This content takes up the remaining space and adjusts dynamically.This content takes up the remaining space and adjusts dynamically.This content takes up the remaining space and adjusts dynamically.This content takes up the remaining space and adjusts dynamically.This content takes up the remaining space and adjusts dynamically.</p>
  </div>
</main>
</body>
</html>

<script>
const toggleButton = document.getElementById('toggleSidebar');
const sidebar = document.getElementById('sidebar');

toggleButton.addEventListener('click', () => {
  sidebar.classList.toggle('collapsed');
});   
</script>

