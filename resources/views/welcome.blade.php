<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Application de gestion de facturation">
    <title>Facturation</title>

    <!-- Google Fonts -->
    <link href="{{ asset('vendor/inter/inter.css') }}" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}">

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
        }

        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .landing-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
            text-align: center;
        }

        .landing-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #0a58ca 100%);
            padding: 3rem 2rem;
            color: white;
        }

        .landing-header i {
            font-size: 4rem;
            margin-bottom: 1rem;
        }

        .landing-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }

        .landing-header p {
            opacity: 0.9;
            margin-top: 0.5rem;
        }

        .landing-body {
            padding: 2rem;
        }

        .feature-list {
            text-align: left;
            margin-bottom: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-item i {
            font-size: 1.25rem;
            margin-right: 1rem;
            width: 32px;
            height: 32px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .feature-item .bi-people { background: rgba(13, 110, 253, 0.1); color: var(--primary-color); }
        .feature-item .bi-file-text { background: rgba(25, 135, 84, 0.1); color: var(--success-color); }
        .feature-item .bi-box-seam { background: rgba(255, 193, 7, 0.1); color: #997404; }

        .btn-primary {
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            width: 100%;
        }

        .btn-outline-secondary {
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            width: 100%;
        }

        .footer-text {
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>

<div class="landing-card">
    <div class="landing-header">
        <i class="bi bi-receipt"></i>
        <h1>Facturation</h1>
        <p>Gestion simplifiee de vos documents</p>
    </div>
    <div class="landing-body">
        <div class="feature-list">
            <div class="feature-item">
                <i class="bi bi-people"></i>
                <span>Gestion des clients</span>
            </div>
            <div class="feature-item">
                <i class="bi bi-file-text"></i>
                <span>Creation de devis et recus</span>
            </div>
            <div class="feature-item">
                <i class="bi bi-box-seam"></i>
                <span>Suivi des produits et stocks</span>
            </div>
        </div>

        <a href="{{ route('login') }}" class="btn btn-primary">
            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
        </a>

        <p class="footer-text">
            Application de gestion de facturation
        </p>
    </div>
</div>

</body>
</html>
