<script>
    $(document).ready(function() {
        var table = $('#user_role').DataTable({
            ajax: {
                url: 'assets/includes/user_role_crud.php',
                type: 'GET',
                data: { crud: 'fetch' }
            },
            columns: [{
                    data: 'id',
                    className: 'text-center'
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'actions',
                    className: 'text-center',
                }
            ],
            autoWidth: false,
            ordering: false,
            stateSave: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'
            }
        });

        $('#adduser_role').on('click', function() {
            $('#user_role_form')[0].reset();
            $('#user_role_crud').val('create');
            $('#user_role_modal_label').text('Agregar rol');
            $('#user_role_modal').modal('show');
        });

        $('#user_role').on('click', '.edit', function() {
            var id = $(this).data('id');
            $('#user_role_form')[0].reset();
            $('#user_role_crud').val('edit');
            $('#user_role_id').val(id);
            $('#user_role_modal_label').text('Editar rol');
            $.ajax({
                url: 'assets/includes/user_role_crud.php',
                type: 'POST',
                data: {
                    crud: 'get',
                    id: id
                },
                dataType: 'json',
                success: function(response) {
                    $('#nombre').val(response.nombre);
                    $('#user_role_modal').modal('show');
                }
            });
        });

        $('#user_role').on('click', '.delete', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: '¡No podrás revertir esto!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: 'assets/includes/user_role_crud.php',
                        data: {
                            crud: 'delete',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            Swal.fire(response.message, '', response.status ? 'success' : 'error');
                            $('#user_role').DataTable().ajax.reload(null, false);
                        }
                    });
                }
            });
        });

        $('#user_role_form').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            $.ajax({
                type: 'POST',
                url: 'assets/includes/user_role_crud.php',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    $('#user_role_modal').modal('hide');
                    Swal.fire(response.message, '', response.status ? 'success' : 'error');
                    $('#user_role').DataTable().ajax.reload(null, false);
                },
                error: function(xhr, status, error) {
                    console.error('Ajax Error:', status, error);
                    Swal.fire('Error', 'Ha ocurrido un error en la solicitud', 'error');
                }
            });
        });
    });
</script>