<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $reportData['title'] }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin: 0 0 10px 0;
            color: #333;
        }

        .header .subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .report-info {
            text-align: right;
            margin-bottom: 20px;
            font-size: 12px;
            color: #666;
        }

        .section {
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }

        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
            border-top: 2px solid #333;
        }

        .summary-row {
            font-weight: bold;
            background-color: #e9e9e9;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $reportData['hotel_name'] }}</h2>
        <h1>{{ $reportData['title'] }}</h1>
        <div class="subtitle">{{ $reportData['filename'] }}</div>
        <div class="subtitle">Generated on {{ date('F j, Y') }}</div>
    </div>

    <div class="report-info">
        <div><strong>Report Type:</strong> {{ ucfirst(str_replace('-', ' ', $reportType)) }}</div>
        <div><strong>Currency:</strong> {{ $reportData['currency']['code'] }} ({{ $reportData['currency']['symbol'] }})</div>
    </div>
</body>
</html>
