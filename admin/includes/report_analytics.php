<?php
// Additional report analytics functions

function generateReportSummary($reportData, $fromdate, $todate) {
    $summary = [
        'total_bookings' => count($reportData),
        'total_revenue' => 0,
        'paid_bookings' => 0,
        'pending_bookings' => 0,
        'failed_bookings' => 0,
        'avg_duration' => 0,
        'most_used_parking' => '',
        'peak_booking_hour' => '',
        'vehicle_categories' => [],
        'payment_methods' => [],
        'daily_breakdown' => []
    ];
    
    $durations = [];
    $parking_usage = [];
    $hourly_bookings = [];
    $vehicle_cats = [];
    $payment_methods = [];
    $daily_revenue = [];
    
    foreach ($reportData as $row) {
        // Revenue calculation
        $amount = $row['payment_amount'] ? $row['payment_amount'] : $row['calculated_amount'];
        
        if ($row['payment_status'] == 'completed' || $row['payment_status'] == 'paid') {
            $summary['total_revenue'] += $amount;
            $summary['paid_bookings']++;
        } elseif ($row['payment_status'] == 'pending') {
            $summary['pending_bookings']++;
        } else {
            $summary['failed_bookings']++;
        }
        
        // Duration tracking
        $durations[] = $row['duration_hours'];
        
        // Parking space usage
        $parking_space = $row['parking_number'];
        $parking_usage[$parking_space] = ($parking_usage[$parking_space] ?? 0) + 1;
        
        // Hourly booking pattern
        $hour = date('H', strtotime($row['start_time']));
        $hourly_bookings[$hour] = ($hourly_bookings[$hour] ?? 0) + 1;
        
        // Vehicle categories
        $cat = $row['VehicleCategory'];
        $vehicle_cats[$cat] = ($vehicle_cats[$cat] ?? 0) + 1;
        
        // Payment methods
        $method = $row['payment_method'] ?: 'Not specified';
        $payment_methods[$method] = ($payment_methods[$method] ?? 0) + 1;
        
        // Daily breakdown
        $date = date('Y-m-d', strtotime($row['start_time']));
        if (!isset($daily_revenue[$date])) {
            $daily_revenue[$date] = ['bookings' => 0, 'revenue' => 0];
        }
        $daily_revenue[$date]['bookings']++;
        if ($row['payment_status'] == 'completed' || $row['payment_status'] == 'paid') {
            $daily_revenue[$date]['revenue'] += $amount;
        }
    }
    
    // Calculate averages and find peaks
    $summary['avg_duration'] = count($durations) > 0 ? array_sum($durations) / count($durations) : 0;
    $summary['most_used_parking'] = !empty($parking_usage) ? array_keys($parking_usage, max($parking_usage))[0] : 'N/A';
    $summary['peak_booking_hour'] = !empty($hourly_bookings) ? array_keys($hourly_bookings, max($hourly_bookings))[0] . ':00' : 'N/A';
    $summary['vehicle_categories'] = $vehicle_cats;
    $summary['payment_methods'] = $payment_methods;
    $summary['daily_breakdown'] = $daily_revenue;
    
    return $summary;
}

function renderAnalyticsCharts($summary) {
    $output = '';
    
    // Vehicle Category Chart Data
    $vehicle_labels = array_keys($summary['vehicle_categories']);
    $vehicle_data = array_values($summary['vehicle_categories']);
    
    // Payment Method Chart Data
    $payment_labels = array_keys($summary['payment_methods']);
    $payment_data = array_values($summary['payment_methods']);
    
    // Daily Revenue Chart Data
    $daily_labels = array_keys($summary['daily_breakdown']);
    $daily_revenue = array_map(function($item) { return $item['revenue']; }, $summary['daily_breakdown']);
    $daily_bookings = array_map(function($item) { return $item['bookings']; }, $summary['daily_breakdown']);
    
    $output .= "
    <script>
    // Vehicle Categories Chart
    const vehicleCategoryData = {
        labels: " . json_encode($vehicle_labels) . ",
        datasets: [{
            data: " . json_encode($vehicle_data) . ",
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
        }]
    };
    
    // Payment Methods Chart
    const paymentMethodData = {
        labels: " . json_encode($payment_labels) . ",
        datasets: [{
            data: " . json_encode($payment_data) . ",
            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
        }]
    };
    
    // Daily Revenue Chart
    const dailyRevenueData = {
        labels: " . json_encode($daily_labels) . ",
        datasets: [{
            label: 'Revenue (KES)',
            data: " . json_encode($daily_revenue) . ",
            borderColor: '#36A2EB',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            yAxisID: 'y'
        }, {
            label: 'Bookings',
            data: " . json_encode($daily_bookings) . ",
            borderColor: '#FF6384',
            backgroundColor: 'rgba(255, 99, 132, 0.1)',
            yAxisID: 'y1'
        }]
    };
    </script>";
    
    return $output;
}
?>
