<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <!-- User List Section -->
    <h2>User List</h2>
    <div id="user-list">
        <!-- User list will be loaded here -->
    </div>
    <button id="load-more" class="btn btn-primary mt-3">Show More</button>

    <!-- Token Display Section -->
    <div class="mt-5">
        <button id="get-token" class="btn btn-info">Get Token</button>
        <p><strong>Token:</strong> <span id="token-display"></span></p>
    </div>

    <!-- Error Display Section -->
    <div class="mt-3">
        <div id="error-message" class="alert alert-danger d-none"></div>
    </div>

    <!-- Success Message Section -->
    <div class="mt-3">
        <div id="success-message" class="alert alert-success d-none"></div>
    </div>

    <!-- User Creation Form -->
    <div class="mt-5">
        <h2>Add New User</h2>
        <form id="user-form" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Profile Image</label>
                <input type="file" class="form-control" id="image" name="image" required>
            </div>
            <button type="submit" class="btn btn-success">Submit</button>
        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
        function displayErrors(errors) {
            let errorHtml = '<ul>';
            $.each(errors, function(key, value) {
                errorHtml += `<li>${value}</li>`;
            });
            errorHtml += '</ul>';
            $('#error-message').html(errorHtml).removeClass('d-none');
        }

        function displaySuccess(message) {
            $('#success-message').html(`<p>${message}</p>`).removeClass('d-none');
        }

        function fetchToken() {
            $.ajax({
                url: '/api/token',
                method: 'GET',
                success: function (response) {
                    $('#token-display').text(response.token);
                },
                error: function () {
                    $('#error-message').html('<p>Failed to get token.</p>').removeClass('d-none');
                }
            });
        }

        function loadUsers(page) {
            $.ajax({
                url: `/api/users?page=${page}`,
                method: 'GET',
                success: function (data) {
                    data.data.forEach(user => {
                        $('#user-list').append(`<p>${user.name} - ${user.email}</p>`);
                    });
                },
                error: function () {
                    $('#error-message').html('<p>Failed to load users.</p>').removeClass('d-none');
                }
            });
        }

        loadUsers(1); // Load first page

        $('#load-more').click(function () {
            let currentPage = $('#user-list').data('page') || 1;
            loadUsers(++currentPage);
            $('#user-list').data('page', currentPage);
        });

        $('#get-token').click(function () {
            fetchToken();
        });

        $('#user-form').submit(function (e) {
            e.preventDefault();

            let formData = new FormData(this);

            $.ajax({
                url: '/api/users',
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${$('#token-display').text()}`
                },
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    displaySuccess('User added successfully!');
                    $('#user-list').prepend(`<p>${response.name} - ${response.email}</p>`);
                    $('#user-form')[0].reset();
                    $('#error-message').addClass('d-none'); // Hide error message
                },
                error: function (response) {
                    let errors = response.responseJSON.errors || {};
                    let message = response.responseJSON.message || 'Error adding user.';
                    displayErrors(errors);
                    $('#error-message').prepend(`<p>${message}</p>`).removeClass('d-none');
                }
            });
        });
    });
</script>
</body>
</html>
