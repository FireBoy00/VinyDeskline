document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('um-overlay');
    const openBtn = document.getElementById('um-add-btn');
    const cancelBtn = document.getElementById('um-cancel');
    const form = document.getElementById('um-add-form');
    const rows = document.getElementById('um-rows');
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (!overlay || !openBtn || !cancelBtn || !form || !rows) return;

    openBtn.addEventListener('click', () => overlay.style.display = 'flex');
    cancelBtn.addEventListener('click', closeModal);

    overlay.addEventListener('click', e => {
        if (e.target === overlay) closeModal();
    });

    function closeModal() {
        overlay.style.display = 'none';
        form.reset();
    }

    function bindRemove(btn) {
        btn.addEventListener('click', async () => {
            const row = btn.closest('.um-row');
            const id = row.dataset.id;

            if (!id) {
                row.remove();
                return;
            }

            await fetch(`/admin/user-management/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrf }
            });

            row.style.opacity = '0';
            setTimeout(() => row.remove(), 300);
        });
    }

    document.querySelectorAll('.um-remove').forEach(bindRemove);

    form.addEventListener('submit', async e => {
        e.preventDefault();

        const payload = {
            name: form.name.value,
            email: form.email.value,
            desk_id: form.desk_id.value,
            status: form.status.value,
            height: form.height.value
        };

        const res = await fetch('/admin/user-management/store', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        const newRow = document.createElement('div');
        newRow.className = 'um-row';
        newRow.dataset.id = data.id;
        newRow.innerHTML = `
            <span class="um-name">${payload.name}</span>
            <div class="um-divider"></div>
            <div class="um-pill-group">
                <button class="um-pill">${payload.desk_id}</button>
                <button class="um-pill">${payload.status}</button>
                <button class="um-pill">${payload.height}</button>
            </div>
            <button class="um-remove">Remove</button>
        `;

        rows.appendChild(newRow);
        bindRemove(newRow.querySelector('.um-remove'));

        closeModal();
    });
});
