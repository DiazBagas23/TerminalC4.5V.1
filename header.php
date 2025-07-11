<?php // header.php ?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klasifikasi Kepadatan Terminal Lubuk Pakam</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .chart-container { position: relative; width: 100%; max-width: 500px; margin-left: auto; margin-right: auto; height: 300px; max-height: 350px; }
        @media (min-width: 768px) { .chart-container { height: 350px; } }
        .rule-tree ul { padding-left: 20px; border-left: 2px solid #e2e8f0; }
        .rule-tree li { position: relative; padding: 8px 0 8px 20px; }
        .rule-tree li::before { content: ''; position: absolute; top: 20px; left: -20px; width: 18px; height: 2px; background-color: #e2e8f0; }
        .nav-link.active { color: #d97706; /* amber-600 */ font-weight: 700; }
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; }
        .alert-success { background-color: #dcfce7; color: #166534; }
        .alert-error { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body class="text-slate-700">
    <header class="bg-white/80 backdrop-blur-lg sticky top-0 z-50 shadow-sm">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <a href="index.php" class="flex items-center space-x-3">
                     <div class="bg-slate-800 p-2 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h1 class="text-lg font-bold text-slate-800">Analisis Kepadatan Terminal</h1>
                </a>
                <nav class="hidden md:flex space-x-6 text-sm font-medium">
                    <a href="index.php" class="nav-link text-slate-600 hover:text-amber-600 transition-colors">Dasbor & Simulasi</a>
                    <a href="data_mentah.php" class="nav-link text-slate-600 hover:text-amber-600 transition-colors">Manajemen Data</a>
                    <a href="proses_mining.php" class="nav-link text-slate-600 hover:text-amber-600 transition-colors">Proses Mining</a>
                </nav>
            </div>
        </div>
    </header>