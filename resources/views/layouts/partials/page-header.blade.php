<div class="page-header">
    <div>
        <h1>{{ $title }}</h1>
        @isset($subtitle)
            <p>{{ $subtitle }}</p>
        @endisset
    </div>
    @isset($actions)
        <div>{{ $actions }}</div>
    @endisset
</div>
