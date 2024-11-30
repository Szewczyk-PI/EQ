async function loadSection(section) {
    const content = document.getElementById('content');
    const response = await fetch(section);
    content.innerHTML = await response.text();
}

async function fetchRecords() {
    // Tutaj umieść funkcję fetchRecords
    try {
        const response = await fetch('api.php');
        if (!response.ok) {
            throw new Error('Błąd sieci lub API: ' + response.status);
        }
        const records = await response.json();
        displayRecords(records);
    } catch (error) {
        console.error('Wystąpił błąd:', error);
        document.getElementById('results').innerText = 'Wystąpił błąd podczas ładowania danych.';
    }
}

async function fetchRecordsWithoutReviewThisYear() {
    // Tutaj umieść funkcję fetchRecordsWithoutReviewThisYear
    try {
        const response = await fetch('api.php?no_review_this_year=true');
        if (!response.ok) {
            throw new Error('Błąd sieci lub API: ' + response.status);
        }
        const records = await response.json();
        const noReviewResults = document.getElementById('noReviewResults');
        if (records.length > 0) {
            let table = `<table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Numer Seryjny</th>
                                    <th>Rodzaj Badania</th>
                                    <th>Obiekt</th>
                                    <th>Firma</th>
                                    <th>Lokalizacja</th>
                                    <th>Producent Stacji</th>
                                    <th>Model Stacji</th>
                                    <th>Data Przeglądu</th>
                                    <th>Data Następnego Przeglądu</th>
                                    <th>Numer Protokołu</th>
                                </tr>
                            </thead>
                            <tbody>`;
            records.forEach(record => {
                table += `
                    <tr>
                        <td>${record.id}</td>
                        <td>${record.numer_seryjny}</td>
                        <td>${record.rodzaj_badania}</td>
                        <td>${record.obiekt}</td>
                        <td>${record.firma}</td>
                        <td>${record.lokalizacja}</td>
                        <td>${record.producent_stacji}</td>
                        <td>${record.model_stacji}</td>
                        <td>${record.data_przegladu || 'Brak'}</td>
                        <td>${record.data_nastepnego_przegladu || 'Brak'}</td>
                        <td>${record.numer_protokolu || 'Brak'}</td>
                    </tr>`;
            });
            table += `</tbody></table>`;
            noReviewResults.innerHTML = table;
        } else {
            noReviewResults.innerHTML = '<p>Brak rekordów bez przeglądu w bieżącym roku.</p>';
        }
    } catch (error) {
        console.error('Wystąpił błąd:', error);
        document.getElementById('noReviewResults').innerText = 'Wystąpił błąd podczas ładowania danych.';
    }
}
