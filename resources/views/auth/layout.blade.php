<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Auth') - RentalBase</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            /* Brand Colors */
            --orange-50: #fff7ed;
            --orange-200: #fed7aa;
            --orange-500: #f97316;
            --orange-600: #ea580c;
            
            /* Surface & Text Colors */
            --white: #ffffff;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-700: #334155;
            --slate-900: #0f172a;
            
            /* Status Colors */
            --red-50: #fef2f2;
            --red-500: #ef4444;
            
            --font-family: 'Plus Jakarta Sans', sans-serif;
            --radius-md: 8px;
            --radius-lg: 12px;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-family);
            background-color: #fffaf5;
            background-image: 
                radial-gradient(circle at 0% 0%, #fff7ed 0%, transparent 60%),
                radial-gradient(circle at 100% 0%, #ffedd5 0%, transparent 60%),
                radial-gradient(circle at 100% 100%, #fed7aa 0%, transparent 60%),
                radial-gradient(circle at 0% 100%, #ffffff 0%, transparent 60%);
            color: var(--slate-900);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 40px;
        }

        /* Navbar placeholder if needed */
        nav {
            width: 100%;
            padding: 24px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .brand-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background-color: var(--orange-500);
            color: var(--white);
            border-radius: 6px;
            font-weight: 800;
            font-size: 16px;
        }
        
        .brand-text {
            font-size: 18px;
            font-weight: 800;
            color: var(--slate-900);
            letter-spacing: -0.02em;
        }
        
        .nav-link {
            color: var(--slate-900);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            transition: color 0.2s;
        }
        
        .nav-link:hover {
            color: var(--orange-500);
        }

        /* Main Container */
        .auth-container {
            width: 100%;
            max-width: 440px;
            padding: 40px 32px;
            margin: auto;
            background-color: var(--white);
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.05), 0 8px 10px -6px rgb(0 0 0 / 0.05);
        }

        .auth-container-wide {
            width: 100%;
            max-width: 768px;
            padding: 40px 32px;
            margin: auto;
            background-color: var(--white);
            border-radius: 20px;
            box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.05), 0 8px 10px -6px rgb(0 0 0 / 0.05);
        }

        h1 {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--slate-900);
            margin-bottom: 8px;
            text-align: center;
        }

        p.sub {
            font-size: 14px;
            color: var(--slate-500);
            text-align: center;
            margin-bottom: 32px;
        }

        /* Google Button */
        .btn-google {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            width: 100%;
            padding: 12px 16px;
            background-color: var(--white);
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-lg);
            color: var(--slate-700);
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: var(--shadow-sm);
        }
        
        .btn-google:hover {
            background-color: var(--slate-50);
            border-color: var(--slate-400);
        }

        .btn-google svg {
            width: 20px;
            height: 20px;
        }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 24px 0;
            font-size: 12px;
            color: var(--slate-400);
            font-weight: 500;
        }
        
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--slate-200);
        }
        
        .divider::before { margin-right: 12px; }
        .divider::after { margin-left: 12px; }

        /* Form Fields */
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field label {
            font-size: 13px;
            font-weight: 700;
            color: var(--slate-700);
        }
        
        .field label i {
            color: var(--orange-500);
            font-style: normal;
        }
        
        /* Input Wrapper for Icon */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-wrapper .icon-left {
            position: absolute;
            left: 12px;
            color: var(--slate-400);
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .input-wrapper .icon-left svg {
            width: 18px;
            height: 18px;
        }

        .input {
            width: 100%;
            padding: 12px 16px 12px 40px; /* space for left icon */
            background-color: var(--white);
            border: 1px solid var(--slate-200);
            border-radius: var(--radius-lg);
            font-family: inherit;
            font-size: 14px;
            color: var(--slate-900);
            transition: all 0.2s;
        }

        .input::placeholder {
            color: var(--slate-400);
        }

        .input:hover {
            border-color: var(--slate-400);
        }

        .input:focus {
            outline: none;
            border-color: var(--orange-500);
            box-shadow: 0 0 0 3px var(--orange-50);
        }
        
        .input.is-invalid {
            border-color: var(--red-500);
        }

        /* Password Wrapper */
        .pw {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .pw input {
            padding-right: 40px;
        }

        .pw button {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: var(--slate-400);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .pw button:hover {
            color: var(--slate-700);
        }
        
        .pw button svg {
            width: 18px;
            height: 18px;
        }

        /* Grid for 2 cols */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        /* Primary Button */
        .btn-primary {
            width: 100%;
            padding: 12px 24px;
            background-color: var(--orange-500);
            color: var(--white);
            border: none;
            border-radius: var(--radius-lg);
            font-family: inherit;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.2s, transform 0.1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }
        
        .btn-primary svg {
            width: 18px;
            height: 18px;
        }

        .btn-primary:hover {
            background-color: var(--orange-600);
        }

        .btn-primary:active {
            transform: translateY(1px);
        }

        /* Row (Remember me & Forgot pass) */
        .row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--slate-700);
            cursor: pointer;
            font-weight: 500;
        }

        .remember input {
            accent-color: var(--orange-500);
            width: 16px;
            height: 16px;
        }

        .link {
            color: var(--orange-500);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            transition: color 0.2s;
        }

        .link:hover {
            color: var(--orange-600);
            text-decoration: underline;
        }

        /* Switch Text */
        p.switch {
            text-align: center;
            margin-top: 32px;
            font-size: 14px;
            color: var(--slate-500);
            font-weight: 500;
        }

        /* Error Messages */
        .error {
            color: var(--red-500);
            font-size: 12px;
            margin-top: 4px;
            font-weight: 500;
        }
        
        .alert {
            background-color: var(--red-50);
            color: var(--red-500);
            padding: 12px 16px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 20px;
        }

        @media (max-width: 480px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav>
        <a href="/" class="brand-logo">
            <div class="brand-icon">R</div>
            <div class="brand-text">RentalBase</div>
        </a>
        @yield('nav-link')
    </nav>
    
    @yield('before-content')
    
    <div class="@yield('container-class', 'auth-container')">
        @yield('content')
    </div>

    <!-- Script for password visibility toggle -->
    <script>
        document.querySelectorAll('[data-toggle-pw]').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-toggle-pw');
                const input = document.getElementById(targetId);
                if (input.type === 'password') {
                    input.type = 'text';
                    this.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24M1 1l22 22"/></svg>';
                } else {
                    input.type = 'password';
                    this.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
                }
            });
        });
    </script>
</body>
</html>
