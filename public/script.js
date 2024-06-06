document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const itemList = document.querySelector('tbody');
    const totalElement = document.querySelector('h2');
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editForm');

    form.addEventListener('submit', function(event) {
        event.preventDefault();1

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
                    editModal.style.display = 'none';
                } else {
                    alert(response.message);
                }
            }
        };
        xhr.send(formData);
    });
});
