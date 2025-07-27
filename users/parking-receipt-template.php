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
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            font-size: 16px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .section table {
            width: 100%;
            border-collapse: collapse;
        }
        .section table td {
            padding: 8px 4px;
        }
        .section table td.label {
            font-weight: bold;
            width: 30%;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Parking Receipt</h1>
    <p>Receipt #: <?= htmlspecialchars($receipt['payment_id']) ?></p>
    <p>Date: <?= date('Y-m-d H:i:s', strtotime($receipt['payment_time'])) ?></p>
</div>

<div class="section">
    <h2>User Information</h2>
    <table>
        <tr>
            <td class="label">Name:</td>
            <td><?= htmlspecialchars($receipt['user_name']) ?></td>
        </tr>
        <tr>
            <td class="label">Phone:</td>
            <td><?= htmlspecialchars($receipt['user_phone']) ?></td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>Vehicle Details</h2>
    <table>
        <tr>
            <td class="label">Model:</td>
            <td><?= htmlspecialchars($receipt['car_model']) ?></td>
        </tr>
        <tr>
            <td class="label">Plate Number:</td>
            <td><?= htmlspecialchars($receipt['car_plate']) ?></td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>Parking Information</h2>
    <table>
        <tr>
            <td class="label">Parking Number:</td>
            <td><?= htmlspecialchars($receipt['parking_number']) ?></td>
        </tr>
        <tr>
            <td class="label">Start Time:</td>
            <td><?= date('Y-m-d H:i', strtotime($receipt['start_time'])) ?></td>
        </tr>
        <tr>
            <td class="label">End Time:</td>
            <td><?= $receipt['end_time'] ? date('Y-m-d H:i', strtotime($receipt['end_time'])) : 'N/A' ?></td>
        </tr>
    </table>
</div>

<div class="section">
    <h2>Transaction Details</h2>
    <table>
        <tr>
            <td class="label">Amount Paid:</td>
            <td>Ksh <?= number_format($receipt['amount'], 2) ?></td>
        </tr>
        <tr>
            <td class="label">Transaction Status:</td>
            <td><?= htmlspecialchars(ucfirst($receipt['payment_status'])) ?></td>
        </tr>
    </table>
</div>

<div class="footer">
    <p>Thank you for using ParkSmart. Drive Safe!</p>
</div>

</body>
</html>