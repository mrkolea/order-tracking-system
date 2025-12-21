<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>Order Number: #{{ $order->orderNumber() }}</p>
    <p>Previous Status: {{ $previousStatus ?? 'N/A' }}</p>
    <p>New Status: {{ $newStatus }}</p>
    <p>Total Amount: ${{ number_format($order->totalAmount(), 2) }}</p>
</body>
</html>
