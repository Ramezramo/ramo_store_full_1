<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم إعادة تعيين كلمة المرور</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md text-center">
        <h2 class="text-2xl font-bold text-green-600 mb-4">تم إعادة تعيين كلمة المرور بنجاح</h2>
        <p id="success-message" class="text-gray-600 mb-6"></p>
        <a href="<?php echo e(url('/login')); ?>"
           class="inline-block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            تسجيل الدخول
        </a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const successMessage = sessionStorage.getItem('resetSuccessMessage') || 'تم إعادة تعيين كلمة المرور بنجاح';
            document.getElementById('success-message').textContent = successMessage;
            sessionStorage.removeItem('resetSuccessMessage');
        });
    </script>
</body>
</html><?php /**PATH /home/runner/workspace/resources/views/auth/reset-password-success.blade.php ENDPATH**/ ?>