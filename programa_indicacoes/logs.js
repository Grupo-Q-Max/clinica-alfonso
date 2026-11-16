document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const dataTable = document.querySelector('.data-table tbody');

    if (searchInput && dataTable) {
        searchInput.addEventListener('keyup', function () {
            const filterText = searchInput.value.toLowerCase();
            const rows = dataTable.querySelectorAll('tr');

            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                if (cells.length > 0) { // Ignora linhas de cabeçalho ou vazias
                    const userName = cells[0].textContent.toLowerCase();
                    const pageVisited = cells[1].textContent.toLowerCase();

                    if (userName.includes(filterText) || pageVisited.includes(filterText)) {
                        row.style.display = ''; // Mostra a linha
                    } else {
                        row.style.display = 'none'; // Esconde a linha
                    }
                }
            });
        });
    }
});
