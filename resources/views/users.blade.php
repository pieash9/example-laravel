<div>
    <!-- Order your soul. Reduce your wants. - Augustine -->
    <h1>Data</h1>
    
    <table border="1" cellpadding="8" cellspacing="0">
        <tr>
            <th>Field</th>
            <th>Value</th>
        </tr>
        <tr>
            <td>ID</td>
            <td>{{ $data->id }}</td>
        </tr>
        <tr>
            <td>Name</td>
            <td>{{ $data->name }}</td>
        </tr>
        <tr>
            <td>Username</td>
            <td>{{ $data->username }}</td>
        </tr>
        <tr>
            <td>Email</td>
            <td>{{ $data->email }}</td>
        </tr>
        <tr>
            <td>Street</td>
            <td>{{ $data->address->street }}</td>
        </tr>
        <tr>
            <td>Suite</td>
            <td>{{ $data->address->suite }}</td>
        </tr>
        <tr>
            <td>City</td>
            <td>{{ $data->address->city }}</td>
        </tr>
        <tr>
            <td>Zipcode</td>
            <td>{{ $data->address->zipcode }}</td>
        </tr>
        <tr>
            <td>Geo (Lat, Lng)</td>
            <td>{{ $data->address->geo->lat }}, {{ $data->address->geo->lng }}</td>
        </tr>
        <tr>
            <td>Phone</td>
            <td>{{ $data->phone }}</td>
        </tr>
        <tr>
            <td>Website</td>
            <td>{{ $data->website }}</td>
        </tr>
        <tr>
            <td>Company</td>
            <td>{{ $data->company->name }}</td>
        </tr>
        <tr>
            <td>Catch Phrase</td>
            <td>{{ $data->company->catchPhrase }}</td>
        </tr>
        <tr>
            <td>Business</td>
            <td>{{ $data->company->bs }}</td>
        </tr>
    </table>
</div>
