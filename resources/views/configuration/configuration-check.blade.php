<style>
    .center {
        margin-left: auto;
        margin-right: auto;
        color: #fff;
    }

    .geslaagd {
        color: green;
    }

    .mislukt {
        color: red;
    }

    .th th {
        border-bottom: 2px solid #fff;
        border-left: 2px solid #fff;
    }

    td {
        border-bottom: 1px solid #fff;
        border-left: 1px solid #fff;
    }
</style>

<table class="center">
    <tr>
        <th colspan=2>
            <h1><strong>Configuration</strong></h1>
        </th>
    </tr>
    <tr class="th" style="text-align:left">
        <th>Test</th>
        <th>Result</th>
    <tr>
        <td>ClamAV</td>
        <td class="@if ($clamavTest == 'Geslaagd') geslaagd @else mislukt @endif">{{ $clamavTest ?? 'Not enabled' }}
        </td>
    </tr>
    <tr>
        <td>Memcahce</td>
        <td class="@if ($memcacheTest == 'Geslaagd') geslaagd @else mislukt @endif">{{ $memcacheTest ?? 'Not enabled' }}
        </td>
    </tr>
    <tr>
        <td>Indexer</td>
        <td class="@if ($indexerTest == 'Geslaagd') geslaagd @else mislukt @endif">{{ $indexerTest ?? 'Not enabled' }}
        </td>
    </tr>
    <tr>
        <td>E-Mail</td>
        <td class="@if ($emailTest == 'Geslaagd') geslaagd @else mislukt @endif">{{ $emailTest ?? 'Not enabled' }}
        </td>
    </tr>
    <tr>
        <td>.env config</td>
        <td class="@if ($envTest == 'Geslaagd') geslaagd @else mislukt @endif">{{ $envTest ?? 'Not enabled' }}
        </td>
    </tr>
    </div>
</table>
