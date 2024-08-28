<table>
    <thead>
        <tr>
            <th>{{ __('No') }} </th>
            <th>{{ __('Name') }} </th>
            <th>{{ __('Status') }} </th>
            <th>{{ __('Role') }} </th>
            <th>{{ __('Email') }} </th>
            <th>{{ __('Phone') }} </th>
            <th>{{ __('City') }} </th>
            <th>{{ __('Created') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($query as $key => $q)
        ?>
        <tr>
            <td>{{ $key + 1 }} </td>
            <td>{{ $q['name'] }} </td>
            <td>{{ $q['status'] == 'P' ? 'Pending' : ($q['status'] == 'A' ? 'Active' : 'InActive') }}</td>
            <td>{{ $q['role'] == 'A' ? 'Admin' : ($q['role'] == 'M' ? 'Manager' : 'Customer') }}</td>
            <td>{{ $q['email'] }} </td>
            <td>{{ $q['phone'] }} </td>
            <td>{{ $q['city'] }}</td>
            <td>{{ $q['created_at'] ? date('Y-m-d H:i:s', strtotime($q['created_at'])) : null }}</td>
        </tr>
        @endforeach
    </tbody>
</table>