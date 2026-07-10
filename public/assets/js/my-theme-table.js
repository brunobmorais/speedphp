document.addEventListener('DOMContentLoaded', function () {
    // Delegação de evento — funciona mesmo com tabelas renderizadas depois
    document.addEventListener('click', function (e) {
        // Só age em mobile
        if (window.innerWidth > 768) return;

        const td = e.target.closest('td:first-child');
        if (!td) return;

        const tr = td.closest('tr');
        if (!tr) return;

        // Ignora linhas de espaçamento e cabeçalho
        if (td.classList.contains('espaco') || tr.closest('thead')) return;

        tr.classList.toggle('expanded');
    });
});

document.querySelectorAll('.table-responsive [data-bs-toggle="dropdown"]').forEach(function(el) {
    var existing = bootstrap.Dropdown.getInstance(el);
    if (existing) existing.dispose();
    new bootstrap.Dropdown(el, { popperConfig: { strategy: 'fixed' } });
});