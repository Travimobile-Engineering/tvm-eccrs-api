<!DOCTYPE html>
<html>
<head>
    <title>Manifest Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Manifest Report</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Manifest Code</th>
                <th>Type</th>
                <th>Location</th>
                <th>Total Passengers</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
            <tr>
                <td>{{ $row['ID'] }}</td>
                <td>{{ $row['Manifest Code'] }}</td>
                <td>{{ $row['Type'] }}</td>
                <td>{{ $row['Location'] }}</td>
                <td>{{ $row['Total Passengers'] }}</td>
                <td>{{ $row['Date'] }}</td>
                <td>{{ $row['Status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
