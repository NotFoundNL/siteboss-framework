@component('mail::message')
    <h4>De volgende query op server {{ $server }} gaf een foutmelding:</h4>
    <p>{{ $query }}
    </p>

    <h4>SOLR meldt het volgende:</h4>
    <p>{{ $result }}</p>
@endcomponent
