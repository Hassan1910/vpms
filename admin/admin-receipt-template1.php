<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Parking Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            color: #007bff;
        }
        .header h2 {
            margin: 5px 0;
            font-size: 20px;
            color: #495057;
        }
        .header p {
            margin: 5px 0;
            color: #6c757d;
        }
        .section {
            margin-bottom: 25px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
        }
        .section h3 {
            font-size: 16px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
            margin-bottom: 15px;
            color: #007bff;
        }
        .section table {
            width: 100%;
            border-collapse: collapse;
        }
        .section table td {
            padding: 8px 4px;
            border-bottom: 1px dotted #dee2e6;
        }
        .section table td:last-child {
            border-bottom: none;
        }
        .section table td.label {
            font-weight: bold;
            width: 35%;
            color: #495057;
        }
        .section table td.value {
            color: #6c757d;
        }
        .amount-section {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            text-align: center;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .amount-section h3 {
            margin: 0;
            font-size: 24px;
        }
        .status-badge {
            background-color: #d4edda;
            color: #155724;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
        }
        .company-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .receipt-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .receipt-meta div {
            flex: 1;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>🚗 Vehicle Parking Management System</h1>
    <h2>Official Payment Receipt</h2>
    <div class="receipt-meta">
        <div>
            <p><strong>Receipt #:</strong> <?= htmlspecialchars($receipt['payment_id']) ?></p>
            <p><strong>Booking ID:</strong> <?= htmlspecialchars($booking_id) ?></p>
        </div>
        <div style="text-align: right;">
            <p><strong>Date Issued:</strong> <?= date('Y-m-d H:i:s', strtotime($receipt['payment_time'])) ?></p>
            <p><strong>Status:</strong> <span class="status-badge"><?= htmlspecialchars(ucfirst($receipt['payment_status'])) ?></span></p>
        </div>
    </div>
</div>

<div class="company-info">
    <h3 style="margin-top: 0; color: #007bff;">Parking Management Office</h3>
    <p><strong>Address:</strong> 123 Main Street, City Center</p>
    <p><strong>Phone:</strong> +254 700 000 000 | <strong>Email:</strong> info@parkingmanagement.com</p>
    <p><strong>Website:</strong> www.parkingmanagement.com</p>
</div>

<div class="section">
    <h3>👤 Customer Information</h3>
    <table>
        <tr>
            <td class="label">Full Name:</td>
            <td class="value"><?= htmlspecialchars($receipt['user_name']) ?></td>
        </tr>
        <tr>
            <td class="label">Phone Number:</td>
            <td class="value"><?= htmlspecialchars($receipt['user_phone']) ?></td>
        </tr>
        <tr>
            <td class="label">Email Address:</td>
            <td class="value"><?= htmlspecialchars($receipt['user_email']) ?></td>
        </tr>
    </table>
</div>

<div class="section">
    <h3>🚙 Vehicle Details</h3>
    <table>
        <tr>
            <td class="label">Registration Number:</td>
            <td class="value"><?= htmlspecialchars($receipt['car_plate']) ?></td>
        </tr>
        <tr>
            <td class="label">Vehicle Model:</td>
            <td class="value"><?= htmlspecialchars($receipt['car_model']) ?></td>
        </tr>
        <tr>
            <td class="label">Vehicle Category:</td>
            <td class="value"><?= htmlspecialchars($receipt['car_category']) ?></td>
        </tr>
    </table>
</div>

<div class="section">
    <h3>🅿️ Parking Information</h3>
    <table>
        <tr>
            <td class="label">Parking Space Number:</td>
            <td class="value"><?= htmlspecialchars($receipt['parking_number']) ?></td>
        </tr>
        <tr>
            <td class="label">Entry Time:</td>
            <td class="value"><?= date('Y-m-d H:i', strtotime($receipt['start_time'])) ?></td>
        </tr>
        <tr>
            <td class="label">Exit Time:</td>
            <td class="value"><?= $receipt['end_time'] ? date('Y-m-d H:i', strtotime($receipt['end_time'])) : 'Ongoing' ?></td>
        </tr>
        <?php if ($receipt['end_time']): ?>
        <tr>
            <td class="label">Duration:</td>
            <td class="value">
                <?php
                $start = new DateTime($receipt['start_time']);
                $end = new DateTime($receipt['end_time']);
                $duration = $start->diff($end);
                echo $duration->format('%d days, %h hours, %i minutes');
                ?>
            </td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<div class="amount-section">
    <h3>💰 Total Amount Paid</h3>
    <h2 style="margin: 10px 0; font-size: 32px;">KES <?= number_format($receipt['amount'], 2) ?></h2>
    <p>Payment processed successfully</p>
</div>

<div class="section">
    <h3>📋 Transaction Summary</h3>
    <table>
        <tr>
            <td class="label">Payment Method:</td>
            <td class="value">Online Payment</td>
        </tr>
        <tr>
            <td class="label">Transaction Date:</td>
            <td class="value"><?= date('F j, Y \a\t g:i A', strtotime($receipt['payment_time'])) ?></td>
        </tr>
        <tr>
            <td class="label">Payment Status:</td>
            <td class="value">
                <span class="status-badge"><?= htmlspecialchars(ucfirst($receipt['payment_status'])) ?></span>
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    <p><strong>Thank you for using our parking services!</strong></p>
    <p>This is a computer-generated receipt and does not require a signature.</p>
    <p>Please keep this receipt for your records.</p>
    <p>For any queries or concerns, please contact our customer service.</p>
    <hr style="margin: 20px 0; border: 1px solid #dee2e6;">
    <p style="font-size: 10px;">
        Generated on <?= date('Y-m-d H:i:s') ?> | 
        Receipt ID: <?= htmlspecialchars($receipt['payment_id']) ?> | 
        System: Vehicle Parking Management System v2.0
    </p>
</div>

</body>
</html>
