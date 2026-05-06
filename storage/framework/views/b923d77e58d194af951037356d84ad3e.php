<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f2f5;
        }
        .container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }
        button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
        }
        .success {
            color: green;
            font-size: 0.9rem;
            margin-top: 0.5rem;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Your Password</h2>
        <div id="success-message" class="success"></div>
        <div id="error-message" class="error"></div>
        <form id="reset-password-form">
            <input type="hidden" id="token" name="token">
            <input type="hidden" id="email" name="email">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>
            <button type="submit">Reset Password</button>
        </form>
    </div>

    <script>
        // Get query parameters from URL
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        const email = urlParams.get('email');

        // Pre-fill hidden inputs with token and email
        document.getElementById('token').value = token || '';
        document.getElementById('email').value = email || '';

        // Handle form submission
        document.getElementById('reset-password-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const password = document.getElementById('password').value;
            const passwordConfirmation = document.getElementById('password_confirmation').value;
            const errorMessage = document.getElementById('error-message');
            const successMessage = document.getElementById('success-message');

            // Reset messages
            errorMessage.style.display = 'none';
            successMessage.style.display = 'none';

            // Basic client-side validation
            if (password !== passwordConfirmation) {
                errorMessage.textContent = 'Passwords do not match.';
                errorMessage.style.display = 'block';
                return;
            }

            if (password.length < 8) {
                errorMessage.textContent = 'Password must be at least 8 characters long.';
                errorMessage.style.display = 'block';
                return;
            }

            // Prepare payload
            const payload = {
                token: token,
                email: email,
                password: password,
                password_confirmation: passwordConfirmation
            };

            try {
                        // $resetUrl = route('password.reset.getter'); // Laravel route helper to get the URL
                        // $baseUrl = AppConstant::
                const response = await fetch('/api/user/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    successMessage.textContent = 'Password reset successfully! You can now log in.';
                    successMessage.style.display = 'block';
                    document.getElementById('reset-password-form').reset();
                } else {
                    const errorData = await response.json();
                    errorMessage.textContent = errorData.message || 'Failed to reset password. Please try again.';
                    errorMessage.style.display = 'block';
                }
            } catch (error) {
                errorMessage.textContent = 'An error occurred. Please try again later.';
                errorMessage.style.display = 'block';
            }
        });
    </script>
</body>
</html><?php /**PATH /home/runner/workspace/resources/views/auth/reset-password.blade.php ENDPATH**/ ?>