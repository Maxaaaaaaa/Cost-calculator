document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const itemList = document.querySelector('tbody');
    const totalElement = document.querySelector('h2');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');

    // Function to generate random color
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    // Initialize Chart.js
    const ctx = document.getElementById('expensesChart').getContext('2d');
    let chartColors = chartData.map(() => getRandomColor());
    let chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: chartData.map(item => item.name),
            datasets: [{
                data: chartData.map(item => item.price),
                backgroundColor: chartColors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Expenses'
                }
            }
        }
    });

    function updateChart() {
        chart.data.labels = chartData.map(item => item.name);
        chart.data.datasets[0].data = chartData.map(item => item.price);
        chart.data.datasets[0].backgroundColor = chartColors;
        chart.update();
    }

    form.addEventListener('submit', function(event) {
        event.preventDefault();





        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'add_item.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const newItem = document.createElement('tr');
                    newItem.innerHTML = `<td>${response.item.name}</td><td>${response.item.price}</td><td><button class="edit" data-id="${response.item.id}">Edit</button><button class="delete" data-id="${response.item.id}">Delete</button></td>`;
                    itemList.appendChild(newItem);
                    totalElement.textContent = `Total: ${response.total}`;
                    chartData.push(response.item);
                    chartColors.push(getRandomColor());
                    updateChart();
                    form.reset();
                } else {
                    alert(response.message);
                }
            }
        };
        xhr.send(formData);
    });

    itemList.addEventListener('click', function(event) {
        if (event.target.classList.contains('edit')) {
            const id = event.target.getAttribute('data-id');
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_item.php?id=${id}`, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        document.getElementById('edit-id').value = response.item.id;
                        document.getElementById('edit-name').value = response.item.name;
                        document.getElementById('edit-price').value = response.item.price;
                        editModal.style.display = 'block';
                    } else {
                        alert(response.message);
                    }
                }
            };
            xhr.send();
        } else if (event.target.classList.contains('delete')) {
            const id = event.target.getAttribute('data-id');
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_item.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        event.target.closest('tr').remove();
                        totalElement.textContent = `Total: ${response.total}`;
                        const itemIndex = chartData.findIndex(item => item.id === parseInt(id));
                        if (itemIndex !== -1) {
                            chartData.splice(itemIndex, 1);
                            chartColors.splice(itemIndex, 1);
                        }
                        updateChart();
                    } else {
                        alert(response.message);
                    }
                }
            };
            xhr.send(`id=${id}`);
        }
    });

    editForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(editForm);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'edit_item.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    const row = document.querySelector(`button[data-id="${response.item.id}"]`).closest('tr');
                    row.children[0].textContent = response.item.name;
                    row.children[1].textContent = response.item.price;
                    totalElement.textContent = `Total: ${response.total}`;
                    const itemIndex = chartData.findIndex(item => item.id === response.item.id);
                    if (itemIndex !== -1) {
                        chartData[itemIndex] = response.item;
                    }
                    updateChart();
                    editModal.style.display = 'none';
                } else {
                    alert(response.message);
                }
            }
        };
        xhr.send(formData);
    });
});
