<script>
    $(document).ready(function() {
        loadTable();

        $('#addmetodo').on('click', function() {
            Swal.fire({
                title: 'Añadir nuevo método',
                html: '<input type="text" id="tipo_metodo" name="tipo_metodo" class="form-control">',
                confirmButtonText: 'Guardar',
                showCancelButton: true,
                preConfirm: () => {
                    const tipo_metodo = Swal.getPopup().querySelector('#tipo_metodo').value;
                    if (!tipo_metodo) {
                        Swal.showValidationMessage('Por favor, completa el campo');
                    }
                    return {
                        tipo_metodo
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    manageRole('create', result.value);
                }
            });
        });

        $('#metodo').on('click', '.edit', function() {
            const id = $(this).data('id');
            $.post('includes/system/metodos_mfa_crud.php', {
                crud: 'get',
                id
            }, function(data) {
                const metodo = JSON.parse(data);
                Swal.fire({
                    title: 'Editar método',
                    html: `
                        <input type="hidden" id="edit-id" value="${metodo.id}">
                        <input type="text" id="edit-tipo_metodo" class="form-control" value="${metodo.tipo_metodo}">
                    `,
                    confirmButtonText: 'Actualizar',
                    showCancelButton: true,
                    preConfirm: () => {
                        const id = Swal.getPopup().querySelector('#edit-id').value;
                        const tipo_metodo = Swal.getPopup().querySelector('#edit-tipo_metodo').value;
                        if (!tipo_metodo) {
                            Swal.showValidationMessage('Por favor, completa el campo');
                        }
                        return {
                            id,
                            tipo_metodo
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        manageRole('edit', result.value);
                    }
                });
            });
        });

        // Add event handler for toggle button
        $('#metodo').on('click', '.toggle', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esto cambiará el estado del método MFA",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, cambiar estado',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    manageRole('toggle', { id });
                }
            });
        });

        function loadTable() {
            const table = $('#metodo').DataTable({
                ajax: 'includes/system/metodos_mfa_crud.php?crud=fetch',
                columns: [{
                        data: 'id',
                        className: 'text-center'
                    },
                    {
                        data: 'tipo_metodo'
                    },
                    {
                        data: 'estado',
                        className: 'text-center'
                    },
                    {
                        data: 'actions',
                        className: 'text-center'
                    }
                ],
                autoWidth: false,
                ordering: false,
                stateSave: true,
                language: {
                    url: '../dist/js/spanish.json'
                }
            });

            table.on('xhr', function() {
                const currentPage = table.page();
                table.page(currentPage).draw(false);
            });
        }

        function manageRole(crud, data) {
            const table = $('#metodo').DataTable();
            const currentPage = table.page();

            $.post('includes/system/metodos_mfa_crud.php', {
                crud,
                ...data
            }, function(response) {
                Swal.fire(response.message, '', response.status ? 'success' : 'error');
                table.ajax.reload(null, false);
            }, 'json');
        }
    });
</script>