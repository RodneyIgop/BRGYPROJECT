function logout(){
    if(confirm('Logout?')) window.location.href='adminLogout.php';
}

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('requestModal');
    const printModal = document.getElementById('printModal');

    const spans = {
        id: document.getElementById('m-requestId'),
        fullname: document.getElementById('m-fullname'),
        ufullname: document.getElementById('m-ufullname'),
        date: document.getElementById('m-date'),
        doc: document.getElementById('m-doc'),
        purpose: document.getElementById('m-purpose'),
        notes: document.getElementById('m-notes'),
        address: document.getElementById('m-address'),
        birthdate: document.getElementById('m-birthdate'),
        age: document.getElementById('m-age'),
        contact: document.getElementById('m-contact'),
        email: document.getElementById('m-email')
    };

    const printSpans = {
        ufullname: document.getElementById('p-ufullname'),
        address: document.getElementById('p-address'),
        birthdate: document.getElementById('p-birthdate'),
        age: document.getElementById('p-age'),
        contact: document.getElementById('p-contact'),
        email: document.getElementById('p-email')
    };

    const printRequestIdField = document.getElementById('printRequestId');
    const validIdInput = document.getElementById('validIdInput');
    const printPreviewFrame = document.getElementById('printPreviewFrame');
    const printForm = document.getElementById('printForm');

    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (!modal) return;
            if (spans.id) spans.id.textContent = btn.dataset.id || '';
            if (spans.fullname) spans.fullname.textContent = btn.dataset.fullname || '';
            if (spans.date) spans.date.textContent = btn.dataset.date || '';
            if (spans.doc) spans.doc.textContent = btn.dataset.doc || '';
            if (spans.purpose) spans.purpose.textContent = btn.dataset.purpose || '';
            if (spans.notes) spans.notes.textContent = (btn.dataset.notes && btn.dataset.notes.trim() !== '') ? btn.dataset.notes : 'None';
            if (spans.ufullname) spans.ufullname.textContent = btn.dataset.fullname || '';
            if (spans.address) spans.address.textContent = btn.dataset.address || '';
            if (spans.birthdate) spans.birthdate.textContent = btn.dataset.birthdate || '';
            if (spans.age) spans.age.textContent = btn.dataset.age || '';
            if (spans.contact) spans.contact.textContent = btn.dataset.contact || '';
            if (spans.email) spans.email.textContent = btn.dataset.email || '';
            modal.style.display = 'flex';
        });
    });

    document.querySelectorAll('.print-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (!printModal) return;

            if (printRequestIdField) printRequestIdField.value = btn.dataset.id || '';
            if (validIdInput) validIdInput.value = '';

            if (printSpans.ufullname) printSpans.ufullname.textContent = btn.dataset.fullname || '';
            if (printSpans.address) printSpans.address.textContent = btn.dataset.address || '';
            if (printSpans.birthdate) printSpans.birthdate.textContent = btn.dataset.birthdate || '';
            if (printSpans.age) printSpans.age.textContent = btn.dataset.age || '';
            if (printSpans.contact) printSpans.contact.textContent = btn.dataset.contact || '';
            if (printSpans.email) printSpans.email.textContent = btn.dataset.email || '';

            if (printPreviewFrame) {
                const id = encodeURIComponent(btn.dataset.id || '');
                printPreviewFrame.src = `adminreleased.php?preview=1&id=${id}`;
            }

            printModal.style.display = 'flex';
        });
    });

    const closeSpan = modal ? modal.querySelector('.close') : null;
    if (closeSpan && modal) {
        closeSpan.addEventListener('click', () => modal.style.display = 'none');
    }

    const printCancelBtn = document.getElementById('printCancelBtn');
    if (printCancelBtn && printModal) {
        printCancelBtn.addEventListener('click', () => {
            printModal.style.display = 'none';
            if (printPreviewFrame) printPreviewFrame.src = '';
        });
    }

    const printClose = document.getElementById('printClose');
    if (printClose && printModal) {
        printClose.addEventListener('click', () => {
            printModal.style.display = 'none';
            if (printPreviewFrame) printPreviewFrame.src = '';
        });
    }

    if (printForm) {
        printForm.addEventListener('submit', (e) => {
            const hasFile = !!(validIdInput && validIdInput.files && validIdInput.files.length > 0);
            if (!hasFile) {
                e.preventDefault();
                alert('Please enter a valid ID.');
                return;
            }
        });
    }

    const declineModal = document.getElementById('declineModal');
    const declineClose = document.querySelector('.decline-close');
    const declineField = document.getElementById('declinePendingId');

    document.querySelectorAll('.reject-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            if (!declineModal || !declineField) return;
            declineField.value = btn.dataset.id || '';
            declineModal.style.display = 'flex';
        });
    });

    if (declineClose && declineModal) {
        declineClose.addEventListener('click', () => declineModal.style.display = 'none');
    }

    window.addEventListener('click', e => {
        if (modal && e.target === modal) modal.style.display = 'none';
        if (declineModal && e.target === declineModal) declineModal.style.display = 'none';
        if (printModal && e.target === printModal) {
            printModal.style.display = 'none';
            if (printPreviewFrame) printPreviewFrame.src = '';
        }
    });

    const searchInput = document.getElementById('approvedSearch');
    const dateInput = document.getElementById('approvedDateFilter');
    const table = document.querySelector('.requests-table table');
    if (table && (searchInput || dateInput)) {
        const pad2 = (n) => String(n).padStart(2,'0');
        const toYmd = (d) => `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;

        const normalizeDateQuery = (s) => {
            const str = (s || '').trim();
            if (!str) return '';
            if (/^\d{4}-\d{2}-\d{2}$/.test(str)) return str;
            const m = str.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (m) return `${m[3]}-${pad2(m[1])}-${pad2(m[2])}`;
            return '';
        };

        const getRowDateYmd = (row) => {
            const cell = row.cells && row.cells.length > 4 ? row.cells[4] : null;
            if (!cell) return '';
            const raw = (cell.textContent || '').trim();
            if (!raw) return '';
            if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw;
            const d = new Date(raw);
            if (Number.isNaN(d.getTime())) return '';
            return toYmd(d);
        };

        const getRowDateText = (row) => {
            const cell = row.cells && row.cells.length > 4 ? row.cells[4] : null;
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

        if (searchInput) searchInput.addEventListener('input', applyFilters);
        if (dateInput) dateInput.addEventListener('input', applyFilters);

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
    }
});