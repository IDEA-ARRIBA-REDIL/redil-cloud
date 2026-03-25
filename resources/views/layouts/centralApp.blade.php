<!DOCTYPE html>
<html lang="es" class="light-style customizer-hide">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>REDIL Cloud - Panel Central</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" />

    <!-- Core CSS (Bootstrap 5 CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    @livewireStyles
    <!-- Custom Styles -->
    <style>
        body { 
            background-color: #f5f5f9; 
            font-family: 'Public Sans', sans-serif; 
            color: #697a8d;
        }
        .card { 
            box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12); 
            border: none; 
            border-radius: .5rem; 
            background-clip: padding-box;
        }
        .table {
            color: #697a8d;
        }
        .table thead th {
            color: #566a7f;
            background-color: #f5f5f9;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        .text-primary, .app-brand-text {
            color: #696cff !important;
        }
        .btn-primary {
            background-color: #696cff;
            border-color: #696cff;
        }
        .btn-primary:hover {
            background-color: #5f61e6;
            border-color: #5f61e6;
        }
        .btn-primary:disabled {
            background-color: #696cff;
            opacity: 0.65;
        }
        .btn-secondary {
            background-color: #8592a3;
            border-color: #8592a3;
        }
        .btn-danger {
            background-color: #ff3e1d;
            border-color: #ff3e1d;
        }
        .bg-label-success {
            background-color: #e8fadf;
            color: #71dd37;
        }
        .bg-label-danger {
            background-color: #ffe0db;
            color: #ff3e1d;
        }
        /* Utils */
        .fw-bolder { font-weight: 700 !important; }
        .text-body { color: #697a8d !important; }
        .cursor-pointer { cursor: pointer; }
        /* Forms */
        .form-control:focus {
            border-color: #696cff;
            box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
        }
        .input-group-text { background-color: transparent; }
    </style>
</head>
<body>
    
    <!-- Content starts here -->
    {{ $slot }}
    <!-- Content ends here -->

    <!-- Core JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    @livewireScripts
</body>
</html>
