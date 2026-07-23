// ============================================================
// Hostel Management System - JavaScript
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    // ----- Sidebar toggle (mobile) -----
    var menuToggle = document.getElementById('menuToggle');
    var sidebar = document.getElementById('sidebar');
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 992 &&
                !sidebar.contains(e.target) &&
                !menuToggle.contains(e.target) &&
                sidebar.classList.contains('open')) {
                sidebar.classList.remove('open');
            }
        });
    }

    // ----- Modal helpers -----
    window.openModal = function (id) {
        var m = document.getElementById(id);
        if (m) m.classList.add('show');
    };
    window.closeModal = function (id) {
        var m = document.getElementById(id);
        if (m) m.classList.remove('show');
    };
    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) overlay.classList.remove('show');
        });
    });

    // ----- Auto-dismiss flash alerts after 5s -----
    var flash = document.getElementById('flashAlert');
    if (flash) {
        setTimeout(function () {
            flash.style.transition = 'opacity 0.5s';
            flash.style.opacity = '0';
            setTimeout(function () { flash.remove(); }, 500);
        }, 5000);
    }

    // ----- Confirm before delete -----
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // ----- Live table search -----
    var searchInputs = document.querySelectorAll('[data-table-search]');
    searchInputs.forEach(function (input) {
        input.addEventListener('keyup', function () {
            var targetId = input.getAttribute('data-table-search');
            var table = document.getElementById(targetId);
            if (!table) return;
            var term = input.value.toLowerCase();
            table.querySelectorAll('tbody tr').forEach(function (row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.indexOf(term) > -1 ? '' : 'none';
            });
        });
    });

    // ----- FAQ toggle -----
    document.querySelectorAll('.faq-item h4').forEach(function (h) {
        h.addEventListener('click', function () {
            var p = h.nextElementSibling;
            if (p) p.style.display = (p.style.display === 'none' || !p.style.display) ? 'block' : 'none';
        });
    });
});
