function openModal(name, email, contact, date, message) {
    document.getElementById("mName").innerText = name;
    document.getElementById("mEmail").innerText = email;
    document.getElementById("mContact").innerText = contact;
    document.getElementById("mDate").innerText = date;
    const messageEl = document.getElementById("mMessage");
    if (messageEl) {
        const safe = String(message)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
        messageEl.innerHTML = `<strong><em>${safe}</em></strong>`;
    }
    document.getElementById("modal").style.display = "flex";
}

function closeModal() {
    document.getElementById("modal").style.display = "none";
}

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', (e) => {
        const a = e.target && e.target.closest ? e.target.closest('a.view-btn') : null;
        if (!a) return;

        e.preventDefault();

        const name = a.getAttribute('data-name') || '';
        const email = a.getAttribute('data-email') || '';
        const contact = a.getAttribute('data-contact') || '';
        const date = a.getAttribute('data-date') || '';
        const message = a.getAttribute('data-message') || '';
        openModal(name, email, contact, date, message);
    });

    const searchInput = document.getElementById('messagesSearch');
    const dateInput = document.getElementById('messagesDateFilter');
    const table = document.querySelector('.requests-table table');
    if(!table || (!searchInput && !dateInput)) return;

    const pad2 = (n) => String(n).padStart(2,'0');
    const toYmd = (d) => `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;

    const normalizeDateQuery = (s) => {
        const str = (s || '').trim();
        if(!str) return '';
        if(/^\d{4}-\d{2}-\d{2}$/.test(str)) return str;
        const m = str.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
        if(m){
            return `${m[3]}-${pad2(m[1])}-${pad2(m[2])}`;
        }
        return '';
    };

    const getRowDateYmd = (row) => {
        const cell = row.cells && row.cells.length > 2 ? row.cells[2] : null;
        if(!cell) return '';
        const raw = (cell.textContent || '').trim();

        if(!raw) return '';
        if(/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw;
        const d = new Date(raw);
        if(Number.isNaN(d.getTime())) return '';
        return toYmd(d);
    };

    const getRowDateText = (row) => {
        const cell = row.cells && row.cells.length > 2 ? row.cells[2] : null;
        return (cell ? (cell.textContent || '') : '').trim();
    };

    const applyFilters = () => {
        const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
        const dateQRaw = (dateInput ? dateInput.value : '').trim();
        const dateQYmd = normalizeDateQuery(dateQRaw);
        const rows = table.tBodies.length ? Array.from(table.tBodies[0].rows) : [];

        rows.forEach(row => {
            const text = (row.textContent || '').toLowerCase();
            const matchesText = !q || text.includes(q);
            const rowDate = dateQYmd ? getRowDateYmd(row) : '';
            const matchesDate = !dateQRaw || (dateQYmd ? (rowDate && rowDate === dateQYmd) : getRowDateText(row).toLowerCase().includes(dateQRaw.toLowerCase()));
            row.style.display = (matchesText && matchesDate) ? '' : 'none';
        });
    };

    if(searchInput) searchInput.addEventListener('input', applyFilters);
    if(dateInput) dateInput.addEventListener('input', applyFilters);

    if (dateInput && window.$ && typeof $(dateInput).datepicker === 'function') {
        $(dateInput).datepicker({
            changeMonth: true,
            changeYear: true,
            yearRange: '1900:+10',
            dateFormat: 'mm/dd/yy',
            onSelect: function () {
                dateInput.dispatchEvent(new Event('input'));
            }
        });
    }
});