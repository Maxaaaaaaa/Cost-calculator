document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#addItemForm');
    const itemList = document.querySelector('#items-table tbody'); // Селектор для таблицы "Item Price Actions"
    const totalElement = document.querySelector('.chart-container h2');
    const balanceElement = document.getElementById('balance');
    const todaySpendingElement = document.getElementById('today-spending');
    const editFormContainer = document.getElementById('editFormContainer');
    const editForm = document.getElementById('editForm');
    const thisMonthModal = document.getElementById('thisMonthModal');
    const closeModal = document.querySelector('.modal-content .close');
    const thisMonthButton = document.getElementById('this-month');

    // Проверка наличия элементов
    console.log('Elements:', {
        form,
        itemList,
        totalElement,
        balanceElement,
        todaySpendingElement,
        editFormContainer,
        editForm,
        thisMonthModal,
        closeModal,
        thisMonthButton
    });

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

    function updateTotal(total) {
        totalElement.textContent = `Total: ${parseFloat(total).toFixed(2)}`;
    }

    function updateBalanceAndTodaySpending(balance, todaySpending) {
        balanceElement.textContent = parseFloat(balance).toFixed(2);
        todaySpendingElement.textContent = parseFloat(todaySpending).toFixed(2);
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
                    updateTotal(response.total);
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
                        editFormContainer.style.display = 'block';
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
                        updateTotal(response.total);
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
                    updateTotal(response.total);
                    const itemIndex = chartData.findIndex(item => item.id === response.item.id);
                    if (itemIndex !== -1) {
                        chartData[itemIndex] = response.item;
                    }
                    updateChart();
                    editFormContainer.style.display = 'none';
                } else {
                    alert(response.message);
                }
            }
        };
        xhr.send(formData);
    });

    // Filter buttons event listeners
    document.getElementById('this-month').addEventListener('click', function() {
        console.log('This Month button clicked');
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_this_month_data.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                console.log('Response from server:', response);
                if (response.success) {
                    document.getElementById('this-month-total').textContent = response.total;
                    document.getElementById('this-month-income').textContent = response.income;
                    document.getElementById('this-month-expenses').textContent = response.expenses;

                    const incomePercentage = (response.income / response.total) * 100;
                    const expensesPercentage = (response.expenses / response.total) * 100;

                    document.getElementById('this-month-income-percentage').textContent = incomePercentage.toFixed(2);
                    document.getElementById('this-month-expenses-percentage').textContent = expensesPercentage.toFixed(2);

                    document.getElementById('income-bar').style.width = incomePercentage + '%';
                    document.getElementById('expenses-bar').style.width = expensesPercentage + '%';

                    // Позиционируем модальное окно под кнопкой "This Month"
                    const rect = thisMonthButton.getBoundingClientRect();
                    thisMonthModal.style.top = `${rect.bottom + window.scrollY}px`;
                    thisMonthModal.style.left = `${rect.left + window.scrollX}px`;

                    thisMonthModal.style.display = 'block';
                } else {
                    alert(response.message);
                }
            } else {
                console.error('Failed to fetch data from server');
            }
        };
        xhr.onerror = function() {
            console.error('Request error');
        };
        xhr.send();
    });

    closeModal.addEventListener('click', function() {
        thisMonthModal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
        if (event.target === thisMonthModal) {
            thisMonthModal.style.display = 'none';
        }
    });

    // Remove any extra "Logout" elements if they exist
    const extraLogoutElements = document.querySelectorAll('.logout-container, .logout-button');
    extraLogoutElements.forEach(element => {
        if (element.textContent.trim() === 'Logout') {
            element.remove();
        }
    });
});
