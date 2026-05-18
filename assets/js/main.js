/* ====== Водить.РФ — фронтенд-логика (vanilla JS) ====== */

/* ---------- Слайдер на главной и в ЛК ---------- */
function initSlider(rootSel) {
    const root = document.querySelector(rootSel);
    if (!root) return;
    const track = root.querySelector('.slides');
    const slides = root.querySelectorAll('.slide');
    const prev = root.querySelector('.slider-btn.prev');
    const next = root.querySelector('.slider-btn.next');
    const dots = root.querySelectorAll('.dot');
    if (!track || slides.length === 0) return;
    let idx = 0;
    let timer = null;

    function go(n) {
        idx = (n + slides.length) % slides.length;
        track.style.transform = `translateX(-${idx * 100}%)`;
        dots.forEach((d, i) => d.classList.toggle('active', i === idx));
    }
    function startAuto() {
        stopAuto();
        timer = setInterval(() => go(idx + 1), 3000);
    }
    function stopAuto() { if (timer) clearInterval(timer); }

    if (prev) prev.addEventListener('click', () => { go(idx - 1); startAuto(); });
    if (next) next.addEventListener('click', () => { go(idx + 1); startAuto(); });
    dots.forEach((d, i) => d.addEventListener('click', () => { go(i); startAuto(); }));

    root.addEventListener('mouseenter', stopAuto);
    root.addEventListener('mouseleave', startAuto);
    go(0);
    startAuto();
}

document.addEventListener('DOMContentLoaded', () => {
    initSlider('#heroSlider');
    initSlider('#profileSlider');
    initRegistrationValidation();
    initApplyForm();
    initRatingInput();
    initAdminTable();
    initToasts();
});

/* ---------- Валидация регистрации ---------- */
function initRegistrationValidation() {
    const f = document.getElementById('regForm');
    if (!f) return;

    const rules = {
        login: v => /^[A-Za-z0-9]{6,}$/.test(v) || 'Минимум 6 символов, латиница и цифры.',
        password: v => v.length >= 8 || 'Минимум 8 символов.',
        fio: v => v.trim().length >= 3 || 'Введите ФИО.',
        birthdate: v => !!v || 'Укажите дату рождения.',
        phone: v => /^\+?[0-9\s\-()]{10,20}$/.test(v) || 'Неверный формат телефона.',
        email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) || 'Неверный e-mail.',
    };

    function check(field) {
        const el = f.elements[field];
        if (!el) return true;
        const err = f.querySelector(`[data-err="${field}"]`);
        const res = rules[field](el.value);
        if (res === true) {
            el.classList.remove('is-invalid');
            if (err) { err.classList.remove('show'); err.textContent = ''; }
            return true;
        }
        el.classList.add('is-invalid');
        if (err) { err.textContent = res; err.classList.add('show'); }
        return false;
    }

    Object.keys(rules).forEach(name => {
        const el = f.elements[name];
        if (el) el.addEventListener('blur', () => check(name));
        if (el) el.addEventListener('input', () => {
            if (el.classList.contains('is-invalid')) check(name);
        });
    });

    f.addEventListener('submit', e => {
        let ok = true;
        Object.keys(rules).forEach(name => { if (!check(name)) ok = false; });
        if (!ok) e.preventDefault();
    });
}

/* ---------- Форма заявки ---------- */
function initApplyForm() {
    const f = document.getElementById('applyForm');
    if (!f) return;
    f.addEventListener('submit', e => {
        const t = f.elements['transport_type'].value;
        const d = f.elements['start_date'].value;
        const p = f.elements['payment_method'].value;
        if (!t || !d || !p) {
            e.preventDefault();
            alert('Заполните все поля.');
        }
    });
}

/* ---------- Звезды рейтинга ---------- */
function initRatingInput() {
    const r = document.querySelector('.rating-input');
    if (!r) return;
    const stars = r.querySelectorAll('.star');
    const input = r.parentElement.querySelector('input[name="rating"]');
    stars.forEach((s, i) => {
        s.addEventListener('click', () => {
            input.value = i + 1;
            stars.forEach((x, j) => x.classList.toggle('active', j <= i));
        });
        s.addEventListener('mouseenter', () => {
            stars.forEach((x, j) => x.classList.toggle('active', j <= i));
        });
    });
    r.addEventListener('mouseleave', () => {
        const v = parseInt(input.value) || 0;
        stars.forEach((x, j) => x.classList.toggle('active', j < v));
    });
}

/* ---------- Сортировка таблицы админки ---------- */
function initAdminTable() {
    const t = document.querySelector('.admin-table');
    if (!t) return;
    const ths = t.querySelectorAll('th.sortable');
    ths.forEach((th, ci) => {
        th.addEventListener('click', () => {
            const tbody = t.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const asc = th.dataset.sortDir !== 'asc';
            rows.sort((a, b) => {
                const av = a.children[ci].innerText.trim().toLowerCase();
                const bv = b.children[ci].innerText.trim().toLowerCase();
                const na = parseFloat(av), nb = parseFloat(bv);
                if (!isNaN(na) && !isNaN(nb)) return asc ? na - nb : nb - na;
                return asc ? av.localeCompare(bv) : bv.localeCompare(av);
            });
            ths.forEach(x => { x.dataset.sortDir = ''; const a = x.querySelector('.sort-arrow'); if (a) a.textContent = '↕'; });
            th.dataset.sortDir = asc ? 'asc' : 'desc';
            const arr = th.querySelector('.sort-arrow');
            if (arr) arr.textContent = asc ? '↑' : '↓';
            rows.forEach(r => tbody.appendChild(r));
        });
    });
}

/* ---------- Тосты ---------- */
function initToasts() {
    document.querySelectorAll('.toast').forEach(el => {
        const t = new bootstrap.Toast(el, { delay: 4000 });
        t.show();
    });
}
