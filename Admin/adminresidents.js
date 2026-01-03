function logout(){
    if(confirm('Logout?')) window.location.href='adminLogout.php';
}

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('residentsSearch');
    const ageInput = document.getElementById('residentsAgeFilter');
    const dateInput = document.getElementById('residentsDateFilter');
    const table = document.querySelector('.requests-table table');
    if(!table || (!searchInput && !ageInput && !dateInput)) return;

    const pad2 = (n) => String(n).padStart(2,'0');
    const toYmd = (d) => `${d.getFullYear()}-${pad2(d.getMonth()+1)}-${pad2(d.getDate())}`;

    const normalizeDateQuery = (s) => {
        const str = (s || '').trim();
        if(!str) return '';
        if(/^(\d{4})-(\d{2})-(\d{2})$/.test(str)) return str;
        const m = str.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
        if(m){
            return `${m[3]}-${pad2(m[1])}-${pad2(m[2])}`;
        }
        return '';
    };

    const getRowAgeText = (row) => {
        const cell = row.cells && row.cells.length > 2 ? row.cells[2] : null;
        return (cell ? (cell.textContent || '') : '').trim();
    };

    const getRowBirthdateYmd = (row) => {
        const cell = row.cells && row.cells.length > 5 ? row.cells[5] : null;
        if(!cell) return '';
        const raw = (cell.textContent || '').trim();
        if(!raw) return '';
        if(/^(\d{4})-(\d{2})-(\d{2})$/.test(raw)) return raw;
        const d = new Date(raw);
        if(Number.isNaN(d.getTime())) return '';
        return toYmd(d);
    };

    const getRowBirthdateText = (row) => {
        const cell = row.cells && row.cells.length > 5 ? row.cells[5] : null;
        return (cell ? (cell.textContent || '') : '').trim();
    };

    const applyFilters = () => {
        const q = (searchInput ? searchInput.value : '').trim().toLowerCase();
        const ageQ = (ageInput ? ageInput.value : '').trim();
        const dateQRaw = (dateInput ? dateInput.value : '').trim();
        const dateQYmd = normalizeDateQuery(dateQRaw);
        const rows = table.tBodies.length ? Array.from(table.tBodies[0].rows) : [];

        rows.forEach(row => {
            const text = (row.textContent || '').toLowerCase();
            const matchesText = !q || text.includes(q);
            const rowAge = getRowAgeText(row);
            const matchesAge = !ageQ || rowAge.toLowerCase().includes(ageQ.toLowerCase());

            const rowBirthdate = dateQYmd ? getRowBirthdateYmd(row) : '';
            const matchesDate = !dateQRaw || (dateQYmd ? (rowBirthdate && rowBirthdate === dateQYmd) : getRowBirthdateText(row).toLowerCase().includes(dateQRaw.toLowerCase()));

            row.style.display = (matchesText && matchesAge && matchesDate) ? '' : 'none';
        });
    };

    if(searchInput) searchInput.addEventListener('input', applyFilters);
    if(ageInput) ageInput.addEventListener('input', applyFilters);
    if(dateInput) dateInput.addEventListener('input', applyFilters);
});
