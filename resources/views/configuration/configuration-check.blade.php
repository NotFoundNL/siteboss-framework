<table id="result">
    <tr>
        <th>Test</th>
        <th>Result</th>
    </tr>
    @foreach ($tests as $test)
        <tr class="result @if ($test->result !== true) error @endif">
            <td>
                @if ($test->result === true)
                    ✅
                @else
                    ❌
                @endif {{ $test->name }}
            </td>
            <td>{{ $test->result === true ? 'Success' : $test->result ?? 'Unkown error' }}
            </td>
        </tr>
    @endforeach
</table>
