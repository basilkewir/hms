@extends('exports.base')

@section('content')
    <div class="section">
        <div class="section-title">Reservations Report</div>

        <table class="table">
            <thead>
                <tr>
                    <th>Confirmation Number</th>
                    <th>Guest Name</th>
                    <th>Guest Email</th>
                    <th>Check-in Date</th>
                    <th>Check-out Date</th>
                    <th>Room Number</th>
                    <th>Room Type</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Booking Source</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservations as $reservation)
                    <tr>
                        <td>{{ $reservation->reservation_number }}</td>
                        <td>{{ $reservation->guest?->full_name ?? 'N/A' }}</td>
                        <td>{{ $reservation->guest?->email ?? 'N/A' }}</td>
                        <td>{{ optional($reservation->check_in_date)->format('Y-m-d') }}</td>
                        <td>{{ optional($reservation->check_out_date)->format('Y-m-d') }}</td>
                        <td>{{ $reservation->room?->room_number ?? '' }}</td>
                        <td>{{ $reservation->roomType?->name ?? '' }}</td>
                        <td class="amount">{{ $reportData['currency']['symbol'] }}{{ number_format($reservation->total_amount, 2) }}</td>
                        <td>{{ $reservation->status }}</td>
                        <td>{{ $reservation->booking_source }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer">
            Total Records: {{ count($reservations) }}
        </div>
    </div>
@endsection
