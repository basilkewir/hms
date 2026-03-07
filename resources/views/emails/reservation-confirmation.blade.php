<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reservation Confirmation</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #1a365d; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 20px; margin-top: 20px; }
        .details { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #1a365d; }
        .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $hotel['name'] }}</h1>
            <p>Reservation Confirmation</p>
        </div>
        
        <div class="content">
            <h2>Dear {{ $reservation->guest->first_name }} {{ $reservation->guest->last_name }},</h2>
            
            <p>Thank you for your reservation. We are pleased to confirm your booking details below:</p>
            
            <div class="details">
                <h3>Reservation Details</h3>
                <p><strong>Confirmation Number:</strong> {{ $reservation->reservation_number }}</p>
                <p><strong>Check-in Date:</strong> {{ $reservation->check_in_date->format('F d, Y') }}</p>
                <p><strong>Check-out Date:</strong> {{ $reservation->check_out_date->format('F d, Y') }}</p>
                <p><strong>Number of Nights:</strong> {{ $reservation->nights }}</p>
                <p><strong>Guests:</strong> {{ $reservation->number_of_adults }} Adult(s), {{ $reservation->number_of_children }} Child(ren)</p>
                @if($reservation->room)
                <p><strong>Room:</strong> {{ $reservation->room->room_number }} - {{ $reservation->roomType->name }}</p>
                @else
                <p><strong>Room Type:</strong> {{ $reservation->roomType->name }}</p>
                @endif
            </div>
            
            <div class="details">
                <h3>Pricing</h3>
                <p><strong>Room Rate:</strong> {{ number_format($reservation->room_rate, 2) }} per night</p>
                <p><strong>Total Room Charges:</strong> {{ number_format($reservation->total_room_charges, 2) }}</p>
                @if($reservation->discount_amount > 0)
                <p><strong>Discount:</strong> -{{ number_format($reservation->discount_amount, 2) }}</p>
                @endif
                <p><strong>Taxes & Service Charges:</strong> {{ number_format($reservation->taxes + $reservation->service_charges, 2) }}</p>
                <p><strong>Total Amount:</strong> {{ number_format($reservation->total_amount, 2) }}</p>
            </div>
            
            @if($reservation->special_requests)
            <div class="details">
                <h3>Special Requests</h3>
                <p>{{ $reservation->special_requests }}</p>
            </div>
            @endif
            
            <p>We look forward to welcoming you to {{ $hotel['name'] }}!</p>
            
            <p>If you have any questions or need to modify your reservation, please contact us:</p>
            <p><strong>Phone:</strong> {{ $hotel['phone'] }}</p>
            <p><strong>Email:</strong> {{ $hotel['email'] }}</p>
        </div>
        
        <div class="footer">
            <p>{{ $hotel['name'] }}</p>
            <p>{{ $hotel['address'] }}</p>
            <p>&copy; {{ date('Y') }} {{ $hotel['name'] }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
