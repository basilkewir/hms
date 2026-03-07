<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $filename }}</title>
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
        <h2>{{ $reportData['hotel_name'] ?? 'Hotel Management System' }}</h2>
        <h1>{{ $reportData['title'] ?? 'Financial Report' }}</h1>
        <div class="subtitle">{{ $filename ?? 'Report' }}</div>
        <div class="subtitle">Generated on {{ date('F j, Y') }}</div>
    </div>

    <div class="report-info">
        <div><strong>Report Type:</strong> {{ ucfirst(str_replace('-', ' ', $reportType ?? 'report')) }}</div>
        <div><strong>Currency:</strong> {{ $reportData['currency']['code'] ?? 'USD' }} ({{ $reportData['currency']['symbol'] ?? '$' }})</div>
    </div>

    @foreach($reportData['sections'] as $sectionKey => $section)
        <div class="section">
            <div class="section-title">{{ $section['title'] }}</div>

            @if($sectionKey === 'revenue' || $sectionKey === 'cogs' || $sectionKey === 'operating_expenses' || $sectionKey === 'current_assets' || $sectionKey === 'fixed_assets' || $sectionKey === 'current_liabilities' || $sectionKey === 'long_term_liabilities' || $sectionKey === 'equity')
                <table class="table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th class="amount">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['items'] as $item)
                            <tr>
                                <td>{{ $item['account'] ?? $item['category'] ?? $item['name'] ?? $item }}</td>
                                <td class="amount">{{ $reportData['currency']['symbol'] }}{{ number_format($item['amount'] ?? $item, 2) }}</td>
                            </tr>
                        @endforeach
                        @if(isset($section['total']))
                            <tr class="total-row">
                                <td><strong>Total {{ $section['title'] }}</strong></td>
                                <td class="amount"><strong>{{ $reportData['currency']['symbol'] }}{{ number_format($section['total'], 2) }}</strong></td>
                            </tr>
                        @endif
                    </tbody>
                </table>

            @elseif($sectionKey === 'gross_profit' || $sectionKey === 'net_profit')
                <table class="table">
                    <tr class="summary-row">
                        <td><strong>{{ $section['title'] }}</strong></td>
                        <td class="amount"><strong>{{ $reportData['currency']['symbol'] }}{{ number_format($section['amount'], 2) }}</strong></td>
                    </tr>
                </table>

            @elseif($sectionKey === 'operating')
                <table class="table">
                    <tr>
                        <td>Net Income</td>
                        <td class="amount">{{ $reportData['currency']['symbol'] }}{{ number_format($section['net_income'], 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>Net Cash from Operating Activities</strong></td>
                        <td class="amount"><strong>{{ $reportData['currency']['symbol'] }}{{ number_format($section['operating_cash_flow'], 2) }}</strong></td>
                    </tr>
                </table>

            @elseif($sectionKey === 'investing' || $sectionKey === 'financing')
                <table class="table">
                    <tr class="total-row">
                        <td><strong>{{ $section['title'] }}</strong></td>
                        <td class="amount"><strong>{{ $reportData['currency']['symbol'] }}{{ number_format($section[$sectionKey . '_cash_flow'], 2) }}</strong></td>
                    </tr>
                </table>

            @elseif($sectionKey === 'summary')
                <table class="table">
                    <tr>
                        <td>Beginning Cash</td>
                        <td class="amount">{{ $reportData['currency']['symbol'] }}{{ number_format($section['beginning_cash'], 2) }}</td>
                    </tr>
                    <tr>
                        <td>Net Cash Change</td>
                        <td class="amount">{{ $reportData['currency']['symbol'] }}{{ number_format($section['net_cash_change'], 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td><strong>Ending Cash</strong></td>
                        <td class="amount"><strong>{{ $reportData['currency']['symbol'] }}{{ number_format($section['ending_cash'], 2) }}</strong></td>
                    </tr>
                </table>

            @elseif($sectionKey === 'revenue_by_category')
                <table class="table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th class="amount">Amount</th>
                            <th class="amount">Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($section['items'] as $item)
                            <tr>
                                <td>{{ $item['category'] }}</td>
                                <td class="amount">{{ $reportData['currency']['symbol'] }}{{ number_format($item['amount'], 2) }}</td>
                                <td class="amount">{{ number_format(($item['amount'] / $section['total_revenue']) * 100, 1) }}%</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td><strong>Total Revenue</strong></td>
                            <td class="amount"><strong>{{ $reportData['currency']['symbol'] }}{{ number_format($section['total_revenue'], 2) }}</strong></td>
                            <td class="amount"><strong>100.0%</strong></td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    @endforeach

    @if(!isset($print_mode))
        <div class="footer">
            This report was generated by the Hotel Management System on {{ date('F j, Y \a\t g:i A') }}.
        </div>
    @endif

    @if(isset($print_mode))
        <script>
            window.onload = function() {
                window.print();
            };
        </script>
    @endif
</body>
</html>
