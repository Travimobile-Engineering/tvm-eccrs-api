<!DOCTYPE html>
<html lang="en">
<head>
    <title>Transport Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Transport Report</h2>
    <table>
        <thead>
            <tr>
                <th>Route</th>
                <th>Mode of Transport</th>
                <th>Passengers</th>
                <th>Trips</th>
                <th>Bookings vs Checkins</th>
                <th>Occupancy Rate</th>
                <th>Inbound Passengers</th>
                <th>Outbound Passengers</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['route'] }}</td>
                <td>{{ $row['mode_of_transport'] }}</td>
                <td>{{ $row['passengers'] }}</td>
                <td>{{ $row['trips'] }}</td>
                <td>{{ $row['bookings_vs_checkins'] }}</td>
                <td>{{ $row['occupancy_rate'] }}</td>
                <td>{{ $row['bound_data']['road']['inbound'] }}</td>
                <td>{{ $row['bound_data']['road']['outbound'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
