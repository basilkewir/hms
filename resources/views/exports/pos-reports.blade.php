@extends('exports.base')

@section('content')
    <div class="section">
        <div class="section-title">POS Reports</div>

        <table class="table">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Transaction Date</th>
                    <th>Customer Name</th>
                    <th>Customer Email</th>
                    <th>Product/Service</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($posReports as $report)
                    <tr>
                        <td>{{ $report->transaction_id }}</td>
                        <td>{{ optional($report->transaction_date)->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $report->customer?->full_name ?? 'N/A' }}</td>
                        <td>{{ $report->customer?->email ?? 'N/A' }}</td>
                        <td>{{ $report->product_name ?? $report->service_name ?? 'N/A' }}</td>
                        <td>{{ $report->quantity ?? 1 }}</td>
                        <td class="amount">{{ $reportData['currency']['symbol'] }}{{ number_format($report->unit_price ?? $report->price, 2) }}</td>
                        <td class="amount">{{ $reportData['currency']['symbol'] }}{{ number_format($report->total_amount, 2) }}</td>
                        <td>{{ $report->payment_method ?? 'N/A' }}</td>
                        <td>{{ $report->status ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Total Records: {{ count($posReports) }}
        </div>
    </div>
@endsection
