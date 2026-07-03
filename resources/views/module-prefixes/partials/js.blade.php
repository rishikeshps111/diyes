<script>
    document.addEventListener('DOMContentLoaded', function () {
        const table = new DataTable('#modulePrefixesTable', {
            processing: true,
            serverSide: true,
            searching: true,
            lengthChange: false,
            order: [[4, 'desc']],
            dom: 'rt<"table_bottom"ip>',
            ajax: '{{ route('module-prefixes.data') }}',
            columns: [
                { data: 'id', name: 'id' },
                { data: 'module_name', name: 'module_name', orderable: false },
                { data: 'prefix', name: 'prefix' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', visible: false, searchable: false }
            ]
        });

        document.getElementById('modulePrefixTableSearch').addEventListener('keyup', function () {
            table.search(this.value).draw();
        });

        document.getElementById('modulePrefixPerPage').addEventListener('change', function () {
            table.page.len(Number(this.value)).draw();
        });
    });
</script>
