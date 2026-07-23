document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    if (toggle && sidebar) {
        toggle.addEventListener('click', () => sidebar.classList.toggle('show'));
    }

    // Attach SweetAlert2 confirmation to every element with data-confirm-delete
    document.querySelectorAll('.js-delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const name = form.dataset.name || 'this item';
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you really want to delete ${name}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d9534f',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
        });
    });

    // Auto-dismiss server-side flash messages after a few seconds
    document.querySelectorAll('.alert-dismissible-auto').forEach(a => {
        setTimeout(() => { a.style.transition = 'opacity .5s'; a.style.opacity = '0'; setTimeout(() => a.remove(), 500); }, 3500);
    });
});
